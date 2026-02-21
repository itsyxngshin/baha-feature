<div class="relative h-screen w-full overflow-hidden bg-gray-100 font-sans" x-data="bahaMap">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div id="map" wire:ignore class="absolute inset-0 z-0 bg-[#1a1a1a]"></div>

    <div class="absolute top-6 left-4 right-4 z-[500]">
        <div class="bg-white rounded-xl shadow-lg flex items-center p-3 border border-gray-100">
            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input wire:model.live="searchQuery" type="text" placeholder="Search Naga City..." class="w-full outline-none text-gray-700 text-sm">
        </div>
        @if(strlen($searchQuery) > 0)
        <div class="mt-2 bg-white rounded-xl shadow-xl overflow-hidden max-h-60 overflow-y-auto border border-gray-100">
            @foreach($filteredHotspots as $spot)
                <div wire:click="selectHotspot({{ $spot->id }}); $set('searchQuery', '')"
                    @click="detailOpen = true; map.flyTo([{{ $spot->latitude }}, {{ $spot->longitude }}], 16);"
                    class="p-4 border-b hover:bg-emerald-50 cursor-pointer flex justify-between">
                    <span class="text-sm font-bold text-gray-700">{{ $spot->name }}</span>
                    <span class="text-[10px] font-bold text-gray-400 uppercase">{{ $spot->status }}</span>
                </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="absolute top-6 right-4 z-[500]">
        <button wire:click="toggleSimulation"
            class="px-4 py-2 rounded-full font-bold text-xs flex items-center gap-2 shadow-lg transition-all"
            :class="$wire.isSimulating ? 'bg-red-500 text-white animate-pulse' : 'bg-white text-gray-700 hover:bg-gray-50'">
            <div class="w-2 h-2 rounded-full" :class="$wire.isSimulating ? 'bg-white' : 'bg-red-500'"></div>
            <span class="hidden md:inline" x-text="$wire.isSimulating ? 'SIMULATION ACTIVE' : 'START SIMULATION'"></span>
        </button>
    </div>

    <div class="absolute bottom-32 right-4 z-[500] flex flex-col gap-3">
        <button @click="locateMe()" class="bg-white text-gray-600 p-3 rounded-xl shadow-lg active:scale-95">
            <svg x-show="!loadingLocation" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
            <svg x-show="loadingLocation" class="animate-spin h-6 w-6 text-blue-500" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        </button>
        <button @click="resetMap()" class="bg-emerald-500 text-white p-3 rounded-xl shadow-lg active:scale-95">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
        </button>
    </div>

    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl shadow-[0_-20px_40px_rgba(0,0,0,0.2)] z-[600] transition-transform duration-300 h-[75vh]"
         :class="detailOpen ? 'translate-y-0' : 'translate-y-[calc(100%-110px)]'">

        <div class="w-full flex justify-center py-4 cursor-pointer" @click="detailOpen = !detailOpen">
            <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
        </div>

        <div class="px-6 pb-24 h-full overflow-y-auto">
            <div wire:loading wire:target="selectHotspot" class="flex flex-col items-center py-12">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-emerald-500"></div>
                <p class="text-[10px] font-bold text-gray-400 mt-4 tracking-widest uppercase">Fetching Prediction...</p>
            </div>

            <div wire:loading.remove wire:target="selectHotspot">
                @if($selectedHotspot)
                    <div class="flex items-center justify-between mb-4">
                        <button wire:click="clearSelection" class="text-[10px] font-black text-emerald-600 uppercase flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            Back to Overview
                        </button>
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest bg-gray-100 px-2 py-1 rounded-md">
                            Updated {{ $selectedHotspot->updated_at->diffForHumans() }}
                        </span>
                    </div>

                    <div class="flex justify-between items-start mb-6">
                        <h2 class="text-2xl font-black text-gray-800 tracking-tight leading-tight">{{ $selectedHotspot->name }}</h2>
                        <div class="text-right">
                            <span class="block text-3xl font-black text-emerald-600">{{ round($selectedHotspot->water_level_cm) }}<span class="text-sm text-gray-400">cm</span></span>
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Prediction</span>
                        </div>
                    </div>

                    <div class="mb-6 bg-white border border-gray-200 shadow-sm rounded-3xl p-5 flex items-stretch gap-6">
                        <div class="relative w-20 h-48 border-l-2 border-b-2 border-gray-300 flex-shrink-0 flex justify-center items-end bg-gray-50/50 rounded-br-lg ml-10">
                            
                            <div class="absolute left-0 bottom-[100%] w-2 border-b-2 border-red-400"></div>
                            <span class="absolute -left-10 bottom-[97%] text-[10px] font-bold text-red-500 w-8 text-right">200cm</span>
                            
                            <div class="absolute left-0 bottom-[85%] w-3 border-b-2 border-gray-500 z-30"></div>
                            <span class="absolute -left-10 bottom-[82%] text-[10px] font-bold text-gray-700 w-8 text-right">170cm</span>
                            
                            <div class="absolute left-0 bottom-[50%] w-2 border-b-2 border-gray-300 z-30"></div>
                            <span class="absolute -left-10 bottom-[47%] text-[10px] font-bold text-gray-400 w-8 text-right">100cm</span>
                            
                            <div class="absolute left-0 bottom-[25%] w-2 border-b-2 border-gray-300 z-30"></div>
                            <span class="absolute -left-10 bottom-[22%] text-[10px] font-bold text-gray-400 w-8 text-right">50cm</span>

                            <div class="absolute bottom-0 w-10 flex justify-center items-end z-10" style="height: 85%;">
                                <svg class="w-full h-full text-gray-400" viewBox="0 0 64 200" fill="currentColor" preserveAspectRatio="none">
                                    <circle cx="32" cy="16" r="16" />
                                    <rect x="18" y="36" width="28" height="70" rx="8" />
                                    <rect x="4" y="36" width="10" height="60" rx="5" />
                                    <rect x="50" y="36" width="10" height="60" rx="5" />
                                    <rect x="20" y="100" width="10" height="100" rx="5" />
                                    <rect x="34" y="100" width="10" height="100" rx="5" />
                                </svg>
                            </div>

                            @php
                                $fillPercentage = min(100, ($selectedHotspot->water_level_cm / 200) * 100);
                            @endphp
                            <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-blue-600/90 to-blue-400/80 transition-all duration-1000 ease-in-out border-t border-blue-300 shadow-[0_-5px_15px_rgba(59,130,246,0.4)] z-20 backdrop-blur-[1px]"
                                style="height: {{ $fillPercentage }}%;">
                            </div>
                        </div>

                        <div class="flex flex-col justify-center py-2">
                            <h5 class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Impact Assessment</h5>
                            <div class="mb-2">
                                @if($selectedHotspot->water_level_cm < 15)
                                    <span class="text-2xl font-black text-gray-800 block leading-none">Puddles</span>
                                    <span class="inline-block px-2 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-md mt-2">✓ Safe to cross</span>
                                @elseif($selectedHotspot->water_level_cm < 50)
                                    <span class="text-2xl font-black text-gray-800 block leading-none">Ankle/Knee Deep</span>
                                    <span class="inline-block px-2 py-1 bg-amber-100 text-amber-700 text-[10px] font-bold rounded-md mt-2">⚠ Caution: Slippery</span>
                                @elseif($selectedHotspot->water_level_cm < 100)
                                    <span class="text-2xl font-black text-gray-800 block leading-none">Waist Deep</span>
                                    <span class="inline-block px-2 py-1 bg-red-100 text-red-700 text-[10px] font-bold rounded-md mt-2">ⓧ Impassable for vehicles</span>
                                @elseif($selectedHotspot->water_level_cm < 170)
                                    <span class="text-2xl font-black text-gray-800 block leading-none">Chest Deep</span>
                                    <span class="inline-block px-2 py-1 bg-red-600 text-white text-[10px] font-bold rounded-md mt-2">☠ Highly Dangerous</span>
                                @else
                                    <span class="text-2xl font-black text-gray-800 block leading-none">Overhead</span>
                                    <span class="inline-block px-2 py-1 bg-red-800 text-white text-[10px] font-bold rounded-md mt-2">☠ Evacuate Immediately</span>
                                @endif
                            </div>
                            <p class="text-[9px] text-gray-400 font-semibold mt-auto">*Based on average 170cm adult height</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-6">
                        <div class="bg-blue-50 p-4 rounded-2xl border border-blue-100 flex flex-col justify-between">
                            <span class="text-[9px] font-bold text-blue-400 uppercase block mb-1">Recorded Rainfall</span>
                            <div class="text-xl font-black text-blue-900">{{ number_format($selectedHotspot->rainfall_mm_hr, 1) }} mm</div>
                        </div>
                        <div class="bg-indigo-50 p-4 rounded-2xl border border-indigo-100 flex flex-col justify-between">
                            <span class="text-[9px] font-bold text-indigo-400 uppercase block mb-1">Prior Rainfall</span>
                            <div class="text-xl font-black text-indigo-900">{{ number_format($selectedHotspot->previous_rainfall_mm, 1) }} mm</div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-200 flex flex-col justify-between">
                            <span class="text-[9px] font-bold text-gray-500 uppercase block mb-1 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                Topography
                            </span>
                            <div class="text-xl font-black text-gray-800">{{ $selectedHotspot->elevation_m ?? 5.0 }} <span class="text-sm font-bold text-gray-500">m ASL</span></div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-200 flex flex-col justify-between">
                            <span class="text-[9px] font-bold text-gray-500 uppercase block mb-1 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                                Drainage
                            </span>
                            <div class="text-xl font-black text-gray-800">{{ $selectedHotspot->drainage_level }}<span class="text-sm font-bold text-gray-500">/10</span></div>
                        </div>
                    </div>

                @else
                    <h3 class="text-lg font-black text-gray-800 mb-4 pt-2 tracking-tight">Active Hotspots in Naga</h3>
                    <div class="space-y-3">
                        @foreach($hotspots as $spot)
                        <div wire:click="selectHotspot({{ $spot->id }})" @click="detailOpen = true; map.flyTo([{{ $spot->latitude }}, {{ $spot->longitude }}], 16)"
                             class="flex items-center justify-between p-4 border border-gray-100 rounded-2xl bg-white hover:bg-emerald-50 transition cursor-pointer shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="w-2.5 h-2.5 rounded-full {{ $spot->status === 'flooded' ? 'bg-red-500 shadow-[0_0_8px_#ef4444]' : ($spot->status === 'moderate' ? 'bg-amber-500' : 'bg-emerald-500') }}"></div>
                                <span class="text-sm font-bold text-gray-700">{{ $spot->name }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-black text-gray-800 block">{{ round($spot->water_level_cm) }}cm</span>
                                <span class="text-[8px] font-bold text-gray-400 uppercase">Level</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    @script
    <script>
        Alpine.data('bahaMap', () => ({
            map: null,
            detailOpen: false,
            loadingLocation: false,
            chart: null,

            init() {
                this.$nextTick(() => { this.initMap(); });
                $wire.on('hotspot-selected', (e) => { this.renderChart(e.prev, e.curr); });

                setInterval(() => {
                    if ($wire.isSimulating) {
                        $wire.$refresh();
                    }
                }, 3000);
            },

            initMap() {
                const bounds = L.latLngBounds([13.55, 123.12], [13.69, 123.28]);
                const nagaBoundary = [[13.68, 123.14], [13.68, 123.27], [13.56, 123.27], [13.56, 123.14]];
                const worldMask = [[-90, -180], [-90, 180], [90, 180], [90, -180]];

                this.map = L.map('map', { zoomControl: false, maxBounds: bounds, minZoom: 13 }).setView([13.621775, 123.194830], 14);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png').addTo(this.map);

                L.polygon([worldMask, nagaBoundary], { fillColor: '#000', fillOpacity: 0.6, weight: 0, interactive: false }).addTo(this.map);

                const locations = @json($hotspots);
                locations.forEach(loc => {
                    let color = loc.status === 'flooded' ? 'bg-red-600' : (loc.status === 'moderate' ? 'bg-amber-500' : 'bg-emerald-500');
                    const icon = L.divIcon({
                        className: '!bg-transparent !border-0',
                        html: `<div class="relative flex items-center justify-center w-8 h-8"><div class="absolute w-full h-full rounded-full opacity-40 animate-pulse ${color}"></div><div class="relative w-3 h-3 rounded-full border-2 border-white shadow-md ${color} z-10"></div></div>`,
                        iconSize: [32, 32], iconAnchor: [16, 16]
                    });

                    L.marker([loc.latitude, loc.longitude], { icon: icon }).addTo(this.map).on('click', async () => {
                        this.map.flyTo([loc.latitude, loc.longitude], 16);
                        await $wire.selectHotspot(loc.id);
                        this.detailOpen = true;
                    });
                });
            },

            renderChart(prev, curr) {
                const ctx = document.getElementById('floodChart');
                if (!ctx) return;
                if (this.chart) this.chart.destroy();
                this.chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Prior', 'Current'],
                        datasets: [{
                            data: [prev, curr],
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { display: false }, x: { grid: { display: false } } } }
                });
            },

            locateMe() {
                this.loadingLocation = true;
                navigator.geolocation.getCurrentPosition((pos) => {
                    this.map.flyTo([pos.coords.latitude, pos.coords.longitude], 17);
                    this.loadingLocation = false;
                }, () => { this.loadingLocation = false; alert("GPS Denied"); });
            },

            resetMap() {
                this.map.flyTo([13.621775, 123.194830], 14);
                this.detailOpen = false;
                $wire.clearSelection();
            }
        }));
    </script>
    @endscript
</div>
