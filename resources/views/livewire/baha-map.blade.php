<div class="relative h-screen w-full overflow-hidden bg-gray-100 font-sans" x-data="bahaMap">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <div id="map" wire:ignore class="absolute inset-0 z-0"></div>

    <div class="absolute top-6 left-4 right-4 z-[500]">
        <div class="bg-white rounded-xl shadow-lg flex items-center p-3 border border-gray-100">
            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input wire:model.live="searchQuery" type="text" placeholder="Search destination in Naga..." class="w-full outline-none text-gray-700 text-sm">
        </div>

        @if(strlen($searchQuery) > 0)
        <div class="mt-2 bg-white rounded-xl shadow-xl overflow-hidden max-h-60 overflow-y-auto border border-gray-100">
            @forelse($filteredHotspots as $spot)
                <div wire:click="selectHotspot({{ $spot->id }}); $set('searchQuery', '')"
                    @click="detailOpen = true; map.flyTo([{{ $spot->latitude }}, {{ $spot->longitude }}], 16);"
                    class="p-4 border-b border-gray-50 hover:bg-emerald-50 cursor-pointer flex justify-between items-center transition">
                    <span class="text-sm font-bold text-gray-700">{{ $spot->name }}</span>
                    <span class="text-[10px] px-2 py-1 rounded-md font-bold uppercase {{ $spot->status === 'flooded' ? 'bg-red-100 text-red-600' : 'bg-emerald-100 text-emerald-600' }}">
                        {{ $spot->status }}
                    </span>
                </div>
            @empty
                <div class="p-4 text-sm text-gray-500 text-center italic">No locations found.</div>
            @endforelse
        </div>
        @endif
    </div>

    <div class="absolute bottom-48 right-4 z-[500] flex flex-col gap-3">
        <button @click="locateMe()" class="bg-white text-gray-600 p-3 rounded-xl shadow-lg hover:text-blue-600 transition active:scale-95">
            <svg x-show="!loadingLocation" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
            <svg x-show="loadingLocation" class="animate-spin h-6 w-6 text-blue-500" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        </button>
        <button @click="resetMap()" class="bg-emerald-500 text-white p-3 rounded-xl shadow-lg hover:bg-emerald-600 transition active:scale-95">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
        </button>
    </div>

    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl shadow-[0_-10px_40px_rgba(0,0,0,0.15)] z-[600] transition-transform duration-300 ease-out transform h-[65vh]"
         :class="detailOpen ? 'translate-y-0' : 'translate-y-[calc(100%-110px)]'">

        <div class="w-full flex justify-center pt-4 pb-4 cursor-pointer" @click="detailOpen = !detailOpen">
            <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
        </div>

        <div class="px-6 pb-24 h-full overflow-y-auto">
            <div wire:loading wire:target="selectHotspot" class="flex flex-col items-center justify-center py-12">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-emerald-500"></div>
                <p class="text-xs font-bold text-gray-400 mt-4 uppercase tracking-widest">Analyzing Zone Data...</p>
            </div>

            <div wire:loading.remove wire:target="selectHotspot">
                @if($selectedHotspot)
                    <div class="flex items-center justify-between mb-2">
                        <button wire:click="clearSelection" class="text-xs text-emerald-600 font-bold hover:underline flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"></path></svg>
                            BACK TO OVERVIEW
                        </button>
                        <span class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">
                            Updated {{ $selectedHotspot->updated_at->diffForHumans() }}
                        </span>
                    </div>

                    <div class="flex justify-between items-start mb-4">
                        <h2 class="text-2xl font-bold text-gray-800 leading-tight">{{ $selectedHotspot->name }}</h2>
                        <div class="text-right">
                            <span class="block text-3xl font-black text-gray-800">{{ round($selectedHotspot->water_level_cm) }}<small class="text-sm font-normal">cm</small></span>
                            <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Water Level</span>
                        </div>
                    </div>

                    <div class="mb-6">
                        @php
                            $statusConfig = [
                                'flooded' => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'label' => 'ⓧ IMPASSABLE'],
                                'moderate' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-600', 'label' => '⚠ CAUTION'],
                                'clear' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600', 'label' => '✓ PASSABLE'],
                            ];
                            $curr = $statusConfig[$selectedHotspot->status] ?? $statusConfig['clear'];
                        @endphp
                        <span class="px-3 py-1 {{ $curr['bg'] }} {{ $curr['text'] }} text-xs font-bold rounded-full">
                            {{ $curr['label'] }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="bg-blue-50 p-4 rounded-2xl border border-blue-100">
                            <div class="flex justify-between items-start mb-1 text-blue-500">
                                <span class="text-xl">💧</span>
                                <span class="text-[9px] font-bold uppercase">Rainfall</span>
                            </div>
                            <div class="text-lg font-bold text-blue-900">{{ number_format($selectedHotspot->rainfall_mm_hr, 1) }} mm</div>
                            <div class="text-[9px] text-blue-400 font-bold uppercase mt-1">Current Hr</div>
                        </div>
                        <div class="bg-indigo-50 p-4 rounded-2xl border border-indigo-100">
                            <div class="flex justify-between items-start mb-1 text-indigo-500">
                                <span class="text-xl">☁️</span>
                                <span class="text-[9px] font-bold uppercase">History</span>
                            </div>
                            <div class="text-lg font-bold text-indigo-900">{{ number_format($selectedHotspot->previous_rainfall_mm, 1) }} mm</div>
                            <div class="text-[9px] text-indigo-400 font-bold uppercase mt-1">Prev Hr</div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100 space-y-4">
                        <h5 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Environmental Factors</h5>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">⛰ Land Elevation</span>
                            <span class="text-sm font-bold text-gray-800">{{ $selectedHotspot->elevation_m ?? '5.0' }}m ASL</span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">🛤 Drainage Quality</span>
                                <span class="font-bold text-gray-800">{{ $selectedHotspot->drainage_level }}/10</span>
                            </div>
                            <div class="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 transition-all duration-1000" style="width: {{ ($selectedHotspot->drainage_level / 10) * 100 }}%"></div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="flex justify-between items-end mb-6 pt-2">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Live Flood Zones</h3>
                            <p class="text-xs text-gray-500">Naga City Hotspot Monitoring</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @foreach($hotspots as $spot)
                        <div wire:click="selectHotspot({{ $spot->id }})" @click="detailOpen = true; map.flyTo([{{ $spot->latitude }}, {{ $spot->longitude }}], 16)"
                             class="flex items-center justify-between p-4 border border-gray-100 rounded-2xl hover:bg-gray-50 transition cursor-pointer shadow-sm bg-white">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $spot->status === 'flooded' ? 'bg-red-50' : ($spot->status === 'moderate' ? 'bg-amber-50' : 'bg-emerald-50') }}">
                                    <div class="w-2.5 h-2.5 rounded-full {{ $spot->status === 'flooded' ? 'bg-red-500' : ($spot->status === 'moderate' ? 'bg-amber-500' : 'bg-emerald-500') }}"></div>
                                </div>
                                <div>
                                    <h5 class="text-sm font-bold text-gray-800">{{ $spot->name }}</h5>
                                    <span class="text-[10px] text-gray-400 font-bold uppercase">{{ $spot->status }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-black text-gray-700 block">{{ round($spot->water_level_cm) }}cm</span>
                                <span class="text-[9px] text-gray-400 uppercase font-bold">Water</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script data-navigate-once>
        document.addEventListener('alpine:init', () => {
            Alpine.data('bahaMap', () => ({
                map: null,
                userMarker: null,
                detailOpen: false,
                loadingLocation: false,

                init() {
                    this.$nextTick(() => { this.initMap(); });
                },

                initMap() {
                    if(this.map) return;
                    this.map = L.map('map', { zoomControl: false }).setView([13.621775, 123.194830], 14);
                    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                        maxZoom: 20,
                        attribution: '© OpenStreetMap'
                    }).addTo(this.map);

                    const locations = @json($hotspots);
                    locations.forEach(loc => {
                        let color = loc.status === 'flooded' ? 'bg-red-600' : (loc.status === 'moderate' ? 'bg-amber-500' : 'bg-emerald-500');

                        const icon = L.divIcon({
                            className: '!bg-transparent !border-0',
                            html: `
                                <div class="relative flex items-center justify-center w-10 h-10">
                                    <div class="absolute w-full h-full rounded-full opacity-40 animate-pulse ${color}"></div>
                                    <div class="relative w-4 h-4 rounded-full border-2 border-white shadow-md ${color} z-10"></div>
                                </div>`,
                            iconSize: [40, 40],
                            iconAnchor: [20, 20]
                        });

                        const marker = L.marker([loc.latitude, loc.longitude], { icon: icon }).addTo(this.map);

                        marker.on('click', async () => {
                            this.map.flyTo([loc.latitude, loc.longitude], 16, { animate: true, duration: 1 });
                            await this.$wire.selectHotspot(loc.id);
                            this.detailOpen = true;
                        });
                    });
                },

                locateMe() {
                    if (!navigator.geolocation) return alert("Geolocation not supported");
                    this.loadingLocation = true;
                    navigator.geolocation.getCurrentPosition((pos) => {
                        const { latitude, longitude } = pos.coords;
                        if (this.userMarker) this.map.removeLayer(this.userMarker);

                        const userIcon = L.divIcon({
                            className: '!bg-transparent !border-0',
                            html: `<div class="w-4 h-4 rounded-full bg-blue-600 border-2 border-white shadow-lg animate-bounce"></div>`,
                            iconSize: [16, 16]
                        });

                        this.userMarker = L.marker([latitude, longitude], { icon: userIcon }).addTo(this.map);
                        this.map.flyTo([latitude, longitude], 17);
                        this.loadingLocation = false;
                    }, () => {
                        this.loadingLocation = false;
                        alert("Please enable GPS permissions.");
                    });
                },

                resetMap() {
                    this.map.flyTo([13.621775, 123.194830], 14);
                    this.detailOpen = false;
                    this.$wire.clearSelection();
                }
            }));
        });
    </script>
</div>
