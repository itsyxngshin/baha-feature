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

    <div class="absolute bottom-48 right-4 z-[500]">
        <button
            @click="locateMe()"
            class="bg-white text-gray-600 hover:text-blue-600 p-3 rounded-xl shadow-lg transition-transform active:scale-95 flex items-center justify-center group"
            :class="loadingLocation ? 'cursor-wait' : ''"
            title="Find my location"
        >
            <svg x-show="loadingLocation" class="animate-spin h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>

            <svg x-show="!loadingLocation" class="w-6 h-6 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
        </button>
    </div>

    <div class="absolute bottom-32 right-4 z-[500]">
        <button @click="resetMap()" class="bg-emerald-500 hover:bg-emerald-600 text-white p-3 rounded-xl shadow-lg transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
        </button>
    </div>

    <div
        class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl shadow-[0_-5px_20px_rgba(0,0,0,0.1)] z-[600] transition-transform duration-300 ease-in-out transform"
        :class="detailOpen ? 'translate-y-0 h-[65vh]' : 'translate-y-[calc(100%-110px)] h-[65vh]'"
    >
        <div class="w-full flex justify-center pt-3 pb-1 cursor-pointer" @click="detailOpen = !detailOpen">
            <div class="w-12 h-1.5 bg-gray-200 rounded-full"></div>
        </div>

        <div class="p-6 h-full overflow-y-auto pb-20">

            @if($selectedHotspot)
                <button wire:click="clearSelection" @click="detailOpen = true" class="text-xs text-emerald-600 font-bold mb-4 flex items-center hover:underline">
                    ← OVERVIEW
                </button>

                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-2xl font-bold text-gray-800 leading-tight">
                        {{ $selectedHotspot->name }} <br> Hotspot
                    </h2>
                    <div class="text-right">
                        <span class="block text-3xl font-black text-gray-800">{{ round($selectedHotspot->water_level_cm) }}cm</span>
                        <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Water Level</span>
                        <span class="text-[10px] text-gray-400 italic block mt-1">
                            updated {{ $selectedHotspot->updated_at->format('g:i A') }}
                        </span>
                    </div>
                </div>

                @if($selectedHotspot->status === 'flooded')
                    <span class="inline-block px-3 py-1 bg-red-100 text-red-600 text-xs font-bold rounded-full mb-6">
                        ⓧ IMPASSABLE
                    </span>
                @elseif($selectedHotspot->status === 'moderate')
                    <span class="inline-block px-3 py-1 bg-amber-100 text-amber-600 text-xs font-bold rounded-full mb-6">
                        ⚠ CAUTION
                    </span>
                @else
                    <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-600 text-xs font-bold rounded-full mb-6">
                        ✓ PASSABLE
                    </span>
                @endif

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <div class="flex justify-between items-start mb-1">
                            <div class="text-blue-500 text-xl">💧</div>
                            <span class="text-[10px] text-gray-400">Past Hr</span>
                        </div>
                        <div class="text-lg font-bold text-gray-800">{{ $selectedHotspot->rainfall_mm_hr }}mm</div>
                        <div class="text-[10px] text-gray-400 uppercase mt-1">
                            Prev: {{ $selectedHotspot->previous_rainfall_mm }}mm
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <div class="text-purple-500 text-xl mb-1">📈</div>
                        <div class="text-lg font-bold text-gray-800">{{ $selectedHotspot->confidence_score }}%</div>
                        <div class="text-[10px] text-gray-400 uppercase">Data Quality</div>
                    </div>
                </div>

                <div class="bg-blue-50 p-4 rounded-2xl mb-6">
                    <h5 class="text-xs font-bold text-blue-800 uppercase mb-2">Location Profile</h5>
                    <div class="flex justify-between text-sm">
                        <span class="text-blue-600">Elevation:</span>
                        <span class="font-bold text-blue-900">{{ $selectedHotspot->elevation_m }}m</span>
                    </div>
                    <div class="flex justify-between text-sm mt-1">
                        <span class="text-blue-600">Drainage Rating:</span>
                        <span class="font-bold text-blue-900">{{ $selectedHotspot->drainage_level }}/10</span>
                    </div>
                </div>

            @else
                <div class="flex justify-between items-end mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-1">Live Flood Zones</h3>
                        <p class="text-sm text-gray-500">Active Hotspot Mapping</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg border border-emerald-100">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1 animate-pulse"></span> Live
                        </span>
                        @if($hotspots->count() > 0)
                        <div class="text-[10px] text-gray-400 mt-1">
                            Updated {{ $hotspots->first()->updated_at->diffForHumans() }}
                        </div>
                        @endif
                    </div>
                </div>

                <div class="flex space-x-4 mb-6 overflow-x-auto pb-2 scrollbar-hide">
                    <div class="min-w-[140px] bg-white border border-gray-100 shadow-sm p-4 rounded-2xl">
                        <span class="text-blue-500 text-xl">🌧️</span>
                        <div class="text-xl font-bold mt-2">{{ round($hotspots->avg('rainfall_mm_hr'), 1) }}mm</div>
                        <div class="text-[10px] text-gray-400 uppercase font-bold tracking-wide">AVG RAINFALL</div>
                    </div>
                    <div class="min-w-[140px] bg-white border border-gray-100 shadow-sm p-4 rounded-2xl">
                        <span class="text-purple-500 text-xl">⚡</span>
                        <div class="text-xl font-bold mt-2">94%</div>
                        <div class="text-[10px] text-gray-400 uppercase font-bold tracking-wide">CONFIDENCE</div>
                    </div>
                </div>

                <h4 class="text-xs font-bold text-gray-400 uppercase mb-3 tracking-widest">Affected Areas</h4>

                <div class="space-y-3">
                    @foreach($hotspots as $spot)
                    <div wire:click="selectHotspot({{ $spot->id }})" @click="detailOpen = true" class="flex items-center justify-between p-4 border border-gray-100 rounded-2xl shadow-sm cursor-pointer hover:bg-gray-50 transition group">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 transition-colors"
                                 style="background-color: {{ $spot->status === 'flooded' ? '#FEE2E2' : ($spot->status === 'moderate' ? '#FEF3C7' : '#D1FAE5') }}">
                                <div class="w-3 h-3 rounded-full shadow-sm" style="background-color: {{ $spot->color }}"></div>
                            </div>
                            <div>
                                <h5 class="text-sm font-bold text-gray-800 group-hover:text-emerald-600 transition">{{ $spot->name }}</h5>
                                <span class="text-xs text-gray-500 flex items-center">
                                    <svg class="w-3 h-3 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    Level: {{ round($spot->water_level_cm) }}cm
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                             @if($spot->status === 'flooded')
                                <span class="text-[10px] font-bold text-red-500 bg-red-50 px-2 py-1 rounded">High Risk</span>
                             @elseif($spot->status === 'moderate')
                                <span class="text-[10px] font-bold text-amber-500 bg-amber-50 px-2 py-1 rounded">Med Risk</span>
                             @else
                                <span class="text-[10px] font-bold text-emerald-500 bg-emerald-50 px-2 py-1 rounded">Safe</span>
                             @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="absolute bottom-0 w-full bg-white border-t border-gray-100 py-2 px-6 flex justify-between items-center z-[700] text-[10px] font-bold text-gray-400 uppercase">
        <a href="#" class="flex flex-col items-center gap-1 hover:text-emerald-600 transition">
            <div class="w-5 h-5 bg-gray-200 rounded"></div> Home
        </a>
        <a href="{{ route('baha.map') }}" class="flex flex-col items-center gap-1 text-emerald-600">
            <div class="w-5 h-5 bg-emerald-100 rounded text-emerald-600 flex items-center justify-center">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
            </div> Baha Map
        </a>
        <a href="#" class="flex flex-col items-center gap-1 hover:text-emerald-600 transition">
            <div class="w-5 h-5 bg-gray-200 rounded"></div> Services
        </a>
        <a href="#" class="flex flex-col items-center gap-1 hover:text-emerald-600 transition">
            <div class="w-5 h-5 bg-gray-200 rounded"></div> Profile
        </a>
    </div>
</div>

<script>
    function mapHandler() {
        return {
            map: null,
            detailOpen: false,
            loadingLocation: false,

            init() {
                this.initMap();

                // Listen for Livewire events to update map dynamically
                Livewire.on('hotspotsUpdated', () => {
                    // Logic to re-render markers if needed (advanced)
                    console.log('Data refreshed');
                });
            },

            initMap() {
                // Initialize Leaflet
                // Centered on Naga City
                this.map = L.map('map', { zoomControl: false }).setView([13.621775, 123.194830], 14);

                // Clean Map Style
                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                    attribution: '&copy; OpenStreetMap &copy; CARTO',
                    maxZoom: 20
                }).addTo(this.map);

                // Load Data
                const locations = @json($hotspots);

                locations.forEach(loc => {
                    // Determine Color (using the logic from your Model or JS fallback)
                    let color = '#10B981'; // Green default
                    if(loc.status === 'flooded') color = '#EF4444';
                    if(loc.status === 'moderate') color = '#F59E0B';

                    // Create Marker
                    const circle = L.circleMarker([loc.latitude, loc.longitude], {
                        color: color,
                        fillColor: color,
                        fillOpacity: 0.6,
                        radius: 14,
                        weight: 2
                    }).addTo(this.map);

                    // Click Event
                    circle.on('click', () => {
                        this.$wire.selectHotspot(loc.id);
                        this.detailOpen = true; // Open the sheet
                    });
                });
            },


            locateMe() {
                if (!navigator.geolocation) {
                    alert("Geolocation is not supported by your browser");
                    return;
                }

                this.loadingLocation = true;

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        const accuracy = position.coords.accuracy;

                        // 1. Remove old marker
                        if (this.userMarker) {
                            this.map.removeLayer(this.userMarker);
                        }

                        // 2. Create Marker using TAILWIND HTML
                        const userIcon = L.divIcon({
                            // Override default Leaflet styles to remove the white square
                            className: '!bg-transparent !border-0',

                            // The Marker HTML: A solid dot with a "pinging" ring behind it
                            html: `
                                <div class="relative flex items-center justify-center w-full h-full">
                                    <span class="absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75 animate-ping"></span>
                                    <span class="relative inline-flex rounded-full h-4 w-4 bg-blue-600 border-2 border-white shadow-sm"></span>
                                </div>
                            `,
                            iconSize: [24, 24], // Size of the container
                            iconAnchor: [12, 12] // Center the icon (half of size)
                        });

                        // 3. Add to map
                        this.userMarker = L.marker([lat, lng], { icon: userIcon }).addTo(this.map);

                        // 4. Fly to user
                        this.map.flyTo([lat, lng], 16, { animate: true, duration: 1.5 });

                        // 5. Popup info
                        this.userMarker.bindPopup(`
                            <div class="text-center">
                                <div class="font-bold text-gray-800">You are here</div>
                                <div class="text-xs text-gray-500">Accuracy: ${Math.round(accuracy)}m</div>
                            </div>
                        `).openPopup();

                        this.loadingLocation = false;
                    },
                    (error) => {
                        this.loadingLocation = false;
                        console.error(error);
                        alert("Could not get location. Ensure GPS is on and HTTPS is used.");
                    },
                    { enableHighAccuracy: true }
                );
            }

            resetMap() {
                this.map.flyTo([13.621775, 123.194830], 14, {
                    animate: true,
                    duration: 1.5
                });
            }
        }
    }
</script>
