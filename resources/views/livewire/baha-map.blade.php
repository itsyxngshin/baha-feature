<div class="relative h-screen w-full overflow-hidden bg-gray-100 font-sans" x-data="bahaMap">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div id="map" wire:ignore class="absolute inset-0 z-0"></div>

    <div class="absolute top-6 left-4 right-4 z-[500]">
        <div class="bg-white rounded-xl shadow-lg flex items-center p-3 border border-gray-100">
            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input wire:model.live="searchQuery" type="text" placeholder="Search destination in Naga..." class="w-full outline-none text-gray-700 text-sm">
            @if(strlen($searchQuery) > 0)
                <button wire:click="$set('searchQuery', '')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            @endif
        </div>

        @if(strlen($searchQuery) > 0)
        <div class="mt-2 bg-white rounded-xl shadow-xl overflow-hidden max-h-60 overflow-y-auto border border-gray-100">
            @forelse($filteredHotspots as $spot)
                <div wire:click="selectHotspot({{ $spot->id }}); $set('searchQuery', '')"
                    @click="detailOpen = true; map.flyTo([{{ $spot->latitude }}, {{ $spot->longitude }}], 16);"
                    class="p-4 border-b border-gray-50 hover:bg-emerald-50 cursor-pointer flex justify-between items-center transition group"
                >
                    <span class="text-sm font-bold text-gray-700 group-hover:text-emerald-700">{{ $spot->name }}</span>
                    <span class="text-[10px] px-2 py-1 rounded-md font-bold uppercase tracking-wider bg-gray-100 text-gray-600">{{ $spot->status }}</span>
                </div>
            @empty
                <div class="p-4 text-sm text-gray-500 text-center italic">No locations found.</div>
            @endforelse
        </div>
        @endif
    </div>

    <div class="absolute bottom-32 left-4 z-[500] bg-white/90 backdrop-blur p-4 rounded-2xl shadow-xl w-40 border border-white/20">
        <h4 class="text-[10px] font-bold text-gray-400 uppercase mb-2 tracking-widest">Risk Levels</h4>
        <div class="space-y-2">
            <div class="flex items-center text-[11px] font-bold text-gray-600"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-2 shadow-[0_0_8px_#10b981]"></span> Clear</div>
            <div class="flex items-center text-[11px] font-bold text-gray-600"><span class="w-2.5 h-2.5 rounded-full bg-amber-500 mr-2 shadow-[0_0_8px_#f59e0b]"></span> Moderate</div>
            <div class="flex items-center text-[11px] font-bold text-gray-600"><span class="w-2.5 h-2.5 rounded-full bg-red-500 mr-2 shadow-[0_0_8px_#ef4444]"></span> Flooded</div>
        </div>
    </div>

    <div class="absolute bottom-32 right-4 z-[500] flex flex-col gap-3">
        <button @click="locateMe()" class="bg-white text-gray-600 p-3 rounded-xl shadow-lg hover:text-blue-600 transition active:scale-90" :title="'My Location'">
            <svg x-show="!loadingLocation" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            <svg x-show="loadingLocation" class="animate-spin h-6 w-6 text-blue-500" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        </button>
        <button @click="resetMap()" class="bg-emerald-500 text-white p-3 rounded-xl shadow-lg hover:bg-emerald-600 transition active:scale-90" :title="'Reset View'">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
        </button>
    </div>

    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl shadow-[0_-10px_40px_rgba(0,0,0,0.15)] z-[600] transition-transform duration-300 ease-out transform h-[75vh]" 
         :class="detailOpen ? 'translate-y-0' : 'translate-y-[calc(100%-110px)]'">
        
        <div class="w-full flex justify-center pt-4 pb-4 cursor-pointer" @click="detailOpen = !detailOpen">
            <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
        </div>

        <div class="px-6 pb-24 h-full overflow-y-auto">
            <div wire:loading wire:target="selectHotspot" class="flex flex-col items-center justify-center py-12">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-emerald-500"></div>
                <p class="text-xs font-bold text-gray-400 mt-4 uppercase tracking-widest">Updating View...</p>
            </div>

            <div wire:loading.remove wire:target="selectHotspot">
                @if($selectedHotspot)
                    <div class="flex items-center justify-between mb-2">
                        <button wire:click="clearSelection" class="text-xs text-emerald-600 font-bold hover:underline uppercase flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"></path></svg> Back to Overview
                        </button>
                        <span class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">Updated {{ $selectedHotspot->updated_at->diffForHumans() }}</span>
                    </div>

                    <div class="flex justify-between items-start mb-4">
                        <h2 class="text-2xl font-bold text-gray-800 leading-tight">{{ $selectedHotspot->name }}</h2>
                        <div class="text-right">
                            <span class="block text-3xl font-black text-gray-800">{{ round($selectedHotspot->water_level_cm) }}<small class="text-sm font-normal text-gray-400 ml-0.5">cm</small></span>
                            <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Water Level</span>
                        </div>
                    </div>

                    <div class="mb-6">
                        @if($selectedHotspot->status === 'flooded')
                            <span class="px-3 py-1 bg-red-100 text-red-600 text-[10px] font-bold rounded-full">ⓧ IMPASSABLE</span>
                        @elseif($selectedHotspot->status === 'moderate')
                            <span class="px-3 py-1 bg-amber-100 text-amber-600 text-[10px] font-bold rounded-full">⚠ CAUTION</span>
                        @else
                            <span class="px-3 py-1 bg-emerald-100 text-emerald-600 text-[10px] font-bold rounded-full">✓ PASSABLE</span>
                        @endif
                    </div>

                    <div class="mb-6 bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <h5 class="text-[10px] font-bold text-gray-400 uppercase mb-4 tracking-widest">Rainfall Trend (mm)</h5>
                        <div class="h-32 w-full"><canvas id="floodChart"></canvas></div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="bg-blue-50 p-4 rounded-2xl border border-blue-100">
                            <div class="flex justify-between items-start mb-1 text-blue-500">
                                <span class="text-xl">💧</span>
                                <span class="text-[9px] font-bold uppercase">Current</span>
                            </div>
                            <div class="text-lg font-bold text-blue-900">{{ number_format($selectedHotspot->rainfall_mm_hr, 1) }} mm</div>
                        </div>
                        <div class="bg-indigo-50 p-4 rounded-2xl border border-indigo-100">
                            <div class="flex justify-between items-start mb-1 text-indigo-500">
                                <span class="text-xl">☁️</span>
                                <span class="text-[9px] font-bold uppercase">Previous</span>
                            </div>
                            <div class="text-lg font-bold text-indigo-900">{{ number_format($selectedHotspot->previous_rainfall_mm, 1) }} mm</div>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-gray-100 space-y-4 shadow-sm">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 font-medium">⛰ Elevation</span>
                            <span class="font-bold text-gray-800">{{ $selectedHotspot->elevation_m ?? 5.0 }}m ASL</span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 font-medium">🛤 Drainage Quality</span>
                                <span class="font-bold text-gray-800">{{ $selectedHotspot->drainage_level }}/10</span>
                            </div>
                            <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500" style="width: {{ ($selectedHotspot->drainage_level / 10) * 100 }}%"></div>
                            </div>
                        </div>
                    </div>
                @else
                    <h3 class="text-xl font-bold text-gray-800 mb-4 pt-2">Live Flood Zones</h3>
                    <div class="space-y-3">
                        @foreach($hotspots as $spot)
                        <div wire:click="selectHotspot({{ $spot->id }})" 
                             @click="detailOpen = true; map.flyTo([{{ $spot->latitude }}, {{ $spot->longitude }}], 16)" 
                             class="flex items-center justify-between p-4 border border-gray-100 rounded-2xl hover:bg-emerald-50 transition cursor-pointer bg-white">
                            <div class="flex items-center gap-3">
                                <div class="w-2.5 h-2.5 rounded-full {{ $spot->status === 'flooded' ? 'bg-red-500 shadow-[0_0_8px_#ef4444]' : ($spot->status === 'moderate' ? 'bg-amber-500 shadow-[0_0_8px_#f59e0b]' : 'bg-emerald-500 shadow-[0_0_8px_#10b981]') }}"></div>
                                <span class="text-sm font-bold text-gray-700">{{ $spot->name }}</span>
                            </div>
                            <span class="text-xs font-bold text-gray-400">{{ round($spot->water_level_cm) }}cm</span>
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
            userMarker: null,
            detailOpen: false,
            loadingLocation: false,
            chart: null,

            init() {
                this.$nextTick(() => { this.initMap(); });

                // Event listener for Chart update
                $wire.on('hotspot-selected', (event) => {
                    this.renderChart(event.prev, event.curr);
                });
            },

            initMap() {
                if(this.map) return;
                this.map = L.map('map', { zoomControl: false }).setView([13.621775, 123.194830], 14);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png').addTo(this.map);

                const locations = @json($hotspots);
                locations.forEach(loc => {
                    let color = loc.status === 'flooded' ? 'bg-red-600' : (loc.status === 'moderate' ? 'bg-amber-500' : 'bg-emerald-500');
                    const icon = L.divIcon({
                        className: '!bg-transparent !border-0',
                        html: `<div class="relative flex items-center justify-center w-10 h-10"><div class="absolute w-full h-full rounded-full opacity-40 animate-pulse ${color}"></div><div class="relative w-4 h-4 rounded-full border-2 border-white shadow-md ${color} z-10"></div></div>`,
                        iconSize: [40, 40], iconAnchor: [20, 20]
                    });

                    const marker = L.marker([loc.latitude, loc.longitude], { icon: icon }).addTo(this.map);
                    marker.on('click', async () => {
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
                        labels: ['Previous Hr', 'Current Hr'],
                        datasets: [{
                            data: [prev, curr],
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 5,
                            pointBackgroundColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, display: false }, x: { grid: { display: false }, ticks: { font: { size: 9 } } } }
                    }
                });
            },

            locateMe() {
                if (!navigator.geolocation) return alert("Geolocation not supported");
                this.loadingLocation = true;
                navigator.geolocation.getCurrentPosition((pos) => {
                    const { latitude, longitude } = pos.coords;
                    if (this.userMarker) this.map.removeLayer(this.userMarker);
                    this.userMarker = L.circleMarker([latitude, longitude], { color: '#3b82f6', fillOpacity: 0.8, radius: 8 }).addTo(this.map);
                    this.map.flyTo([latitude, longitude], 17);
                    this.loadingLocation = false;
                }, () => { this.loadingLocation = false; alert("GPS access denied."); });
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