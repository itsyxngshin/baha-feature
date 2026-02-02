<div class="relative h-screen w-full overflow-hidden bg-gray-100 font-sans" x-data="mapHandler()">

    <div class="absolute top-6 left-4 right-4 z-[500]">
        <div class="bg-white rounded-xl shadow-lg flex items-center p-3">
            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input wire:model.live="searchQuery" type="text" placeholder="Search destination in Naga..." class="w-full outline-none text-gray-700 placeholder-gray-400 text-sm">
        </div>
    </div>

    <div id="map" wire:ignore class="h-full w-full z-0"></div>

    <div class="absolute bottom-32 left-4 z-[500] bg-white p-4 rounded-2xl shadow-xl w-40">
        <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">Risk Hotspots</h4>
        <div class="space-y-2">
            <div class="flex items-center text-xs font-semibold text-gray-700">
                <span class="w-3 h-3 rounded-full bg-emerald-500 mr-2"></span> Clear
            </div>
            <div class="flex items-center text-xs font-semibold text-gray-700">
                <span class="w-3 h-3 rounded-full bg-amber-500 mr-2"></span> Moderate
            </div>
            <div class="flex items-center text-xs font-semibold text-gray-700">
                <span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span> Flooded
            </div>
        </div>
    </div>

    <div class="absolute bottom-32 right-4 z-[500]">
        <button class="bg-emerald-500 hover:bg-emerald-600 text-white p-3 rounded-xl shadow-lg transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
        </button>
    </div>

    <div 
        class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl shadow-[0_-5px_20px_rgba(0,0,0,0.1)] z-[600] transition-transform duration-300 ease-in-out transform"
        :class="detailOpen ? 'translate-y-0 h-[60vh]' : 'translate-y-[calc(100%-80px)] h-auto'"
    >
        <div class="w-full flex justify-center pt-3 pb-1" @click="detailOpen = !detailOpen">
            <div class="w-12 h-1.5 bg-gray-200 rounded-full"></div>
        </div>

        <div class="p-6 h-full overflow-y-auto">
            
            @if($selectedHotspot)
                <button wire:click="clearSelection" class="text-xs text-emerald-600 font-bold mb-4 flex items-center">
                    ← OVERVIEW
                </button>

                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-2xl font-bold text-gray-800 leading-tight">
                        {{ $selectedHotspot->name }} <br> Hotspot
                    </h2>
                    <div class="text-right">
                        <span class="block text-2xl font-black text-gray-800">{{ $selectedHotspot->water_level_cm }}cm</span>
                        <span class="text-[10px] text-gray-400 uppercase">Water Level</span>
                    </div>
                </div>

                @if($selectedHotspot->status === 'flooded')
                    <span class="inline-block px-3 py-1 bg-red-100 text-red-600 text-xs font-bold rounded-full mb-6">
                        ⓧ IMPASSABLE
                    </span>
                @endif

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 p-4 rounded-2xl">
                        <div class="text-emerald-500 text-xl mb-1">💧</div>
                        <div class="text-lg font-bold text-gray-800">{{ $selectedHotspot->rainfall_mm }}mm</div>
                        <div class="text-[10px] text-gray-400 uppercase">Rainfall</div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-2xl">
                        <div class="text-purple-500 text-xl mb-1">📈</div>
                        <div class="text-lg font-bold text-gray-800">{{ $selectedHotspot->confidence_score }}%</div>
                        <div class="text-[10px] text-gray-400 uppercase">Prediction Conf.</div>
                    </div>
                </div>

            @else
                <h3 class="text-xl font-bold text-gray-800 mb-1">Live Flood Zones</h3>
                <p class="text-sm text-gray-500 mb-6">Active Hotspot Mapping</p>

                <div class="flex space-x-4 mb-6 overflow-x-auto">
                    <div class="min-w-[140px] bg-white border border-gray-100 shadow-sm p-4 rounded-2xl">
                        <span class="text-blue-500 text-xl">🌧️</span>
                        <div class="text-xl font-bold mt-2">12.4mm</div>
                        <div class="text-xs text-gray-400">AVG RAINFALL</div>
                    </div>
                    <div class="min-w-[140px] bg-white border border-gray-100 shadow-sm p-4 rounded-2xl">
                        <span class="text-purple-500 text-xl">⚡</span>
                        <div class="text-xl font-bold mt-2">94%</div>
                        <div class="text-xs text-gray-400">CONFIDENCE</div>
                    </div>
                </div>

                <h4 class="text-xs font-bold text-gray-400 uppercase mb-3">Affected Areas</h4>
                
                <div class="space-y-3">
                    @foreach($hotspots as $spot)
                    <div wire:click="selectHotspot({{ $spot->id }})" class="flex items-center justify-between p-4 border border-gray-100 rounded-2xl shadow-sm cursor-pointer hover:bg-gray-50 transition">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3" style="background-color: {{ $spot->status === 'flooded' ? '#FEE2E2' : '#D1FAE5' }}">
                                <div class="w-3 h-3 rounded-full" style="background-color: {{ $spot->color }}"></div>
                            </div>
                            <div>
                                <h5 class="text-sm font-bold text-gray-800">{{ $spot->name }}</h5>
                                <span class="text-xs text-gray-500">Est. Depth: {{ $spot->water_level_cm }}cm</span>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="absolute bottom-0 w-full bg-white border-t border-gray-100 py-2 px-6 flex justify-between items-center z-[700] text-[10px] font-bold text-gray-400 uppercase">
        <div class="flex flex-col items-center gap-1">
            <div class="w-5 h-5 bg-gray-200 rounded"></div> Home
        </div>
        <div class="flex flex-col items-center gap-1 text-emerald-600">
            <div class="w-5 h-5 bg-emerald-100 rounded"></div> Baha Map
        </div>
        <div class="flex flex-col items-center gap-1">
            <div class="w-5 h-5 bg-gray-200 rounded"></div> Services
        </div>
        <div class="flex flex-col items-center gap-1">
            <div class="w-5 h-5 bg-gray-200 rounded"></div> Profile
        </div>
    </div>
</div>

<script>
    function mapHandler() {
        return {
            map: null,
            detailOpen: false,
            init() {
                // Initialize Leaflet
                this.map = L.map('map', { zoomControl: false }).setView([13.621775, 123.194830], 14); // Naga City Coords

                // Add Tile Layer (using CartoDB Voyager for that clean look)
                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                    attribution: '&copy; OpenStreetMap &copy; CARTO',
                    subdomains: 'abcd',
                    maxZoom: 20
                }).addTo(this.map);

                // Load Markers from Livewire Data
                const locations = @json($hotspots);
                
                locations.forEach(loc => {
                    // Create Custom Colored Circle Marker
                    const circle = L.circleMarker([loc.latitude, loc.longitude], {
                        color: loc.status === 'flooded' ? '#EF4444' : (loc.status === 'moderate' ? '#F59E0B' : '#10B981'),
                        fillColor: loc.status === 'flooded' ? '#EF4444' : (loc.status === 'moderate' ? '#F59E0B' : '#10B981'),
                        fillOpacity: 0.5,
                        radius: 12 // Large radius for the "glow" look
                    }).addTo(this.map);

                    // Add click event to talk to Livewire
                    circle.on('click', () => {
                        this.$wire.selectHotspot(loc.id);
                        this.detailOpen = true; // Open bottom sheet
                    });
                });
            }
        }
    }
</script>