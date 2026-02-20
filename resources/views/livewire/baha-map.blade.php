<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="relative h-screen w-full overflow-hidden bg-gray-100 font-sans"
     x-data="{
        map: null,
        userMarker: null,
        detailOpen: false,
        loadingLocation: false,

        initMap() {
            if(this.map) return;

            this.map = L.map('map', { zoomControl: false }).setView([13.621775, 123.194830], 14);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 20 }).addTo(this.map);

            const locations = @json($hotspots);

            locations.forEach(loc => {
                if (!loc.latitude || !loc.longitude) return;

                let colorClass = loc.status === 'flooded' ? 'bg-red-600' : (loc.status === 'moderate' ? 'bg-amber-500' : 'bg-emerald-500');
                let shadowClass = loc.status === 'flooded' ? 'shadow-[0_0_30px_rgba(220,38,38,1)]' : (loc.status === 'moderate' ? 'shadow-[0_0_20px_rgba(245,158,11,0.8)]' : 'shadow-[0_0_20px_rgba(16,185,129,0.8)]');

                const glowIcon = L.divIcon({
                    className: '!bg-transparent !border-0',
                    html: `<div class='relative flex items-center justify-center w-10 h-10'><div class='absolute w-full h-full rounded-full opacity-60 animate-pulse ${colorClass} ${shadowClass}'></div><div class='relative w-4 h-4 rounded-full border-2 border-white ${colorClass} z-10'></div></div>`,
                    iconSize: [40, 40],
                    iconAnchor: [20, 20]
                });

                const marker = L.marker([loc.latitude, loc.longitude], { icon: glowIcon }).addTo(this.map);

                // BULLETPROOF LIVEWIRE CONNECTION
                marker.on('click', async () => {
                    // Tell PHP to fetch the exact hotspot data
                    await this.$wire.selectHotspot(loc.id);
                    // Slide the panel up
                    this.detailOpen = true;
                });
            });
        },

        locateMe() {
            if (!navigator.geolocation) return alert('Geolocation is not supported');
            this.loadingLocation = true;
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    if (this.userMarker) this.map.removeLayer(this.userMarker);
                    const userIcon = L.divIcon({
                        className: '!bg-transparent !border-0',
                        html: `<div class='relative flex items-center justify-center w-6 h-6'><span class='absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75 animate-ping'></span><span class='relative inline-flex rounded-full h-4 w-4 bg-blue-600 border-2 border-white shadow-sm'></span></div>`,
                        iconSize: [24, 24], iconAnchor: [12, 12]
                    });
                    this.userMarker = L.marker([lat, lng], { icon: userIcon }).addTo(this.map);
                    this.map.flyTo([lat, lng], 16, { animate: true, duration: 1.5 });
                    this.loadingLocation = false;
                },
                () => { this.loadingLocation = false; alert('Location failed.'); },
                { enableHighAccuracy: true }
            );
        },

        resetMap() {
            this.map.flyTo([13.621775, 123.194830], 14, { animate: true, duration: 1.5 });
        }
     }"
     x-init="$nextTick(() => { initMap() })"
>

    <div class="absolute top-6 left-4 right-4 z-[500]">
        <div class="bg-white rounded-xl shadow-lg flex items-center p-3">
            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input wire:model.live="searchQuery" type="text" placeholder="Search destination in Naga..." class="w-full outline-none text-gray-700 placeholder-gray-400 text-sm">
            @if(strlen($searchQuery) > 0)
            <button wire:click="$set('searchQuery', '')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            @endif
        </div>

        @if(strlen($searchQuery) > 0 && isset($filteredHotspots))
        <div class="mt-2 bg-white rounded-xl shadow-lg overflow-hidden max-h-60 overflow-y-auto border border-gray-100">
            @forelse($filteredHotspots as $spot)
                <div wire:click="selectHotspot({{ $spot->id }}); $set('searchQuery', '')" @click="detailOpen = true; map.flyTo([{{ $spot->latitude ?? 13.621775 }}, {{ $spot->longitude ?? 123.194830 }}], 16, {animate: true, duration: 1});" class="p-4 border-b border-gray-50 hover:bg-emerald-50 cursor-pointer flex justify-between items-center transition">
                    <span class="text-sm font-bold text-gray-700">{{ $spot->name }}</span>
                    <span class="text-[10px] px-2 py-1 rounded-md font-bold uppercase tracking-wider bg-gray-100 text-gray-600">{{ $spot->status }}</span>
                </div>
            @empty
                <div class="p-4 text-sm text-gray-500 text-center italic">No locations found.</div>
            @endforelse
        </div>
        @endif
    </div>

    <div id="map" wire:ignore class="absolute inset-0 z-0"></div>

    <div class="absolute bottom-32 left-4 z-[500] bg-white p-4 rounded-2xl shadow-xl w-40">
        <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">Risk Hotspots</h4>
        <div class="space-y-2">
            <div class="flex items-center text-xs font-semibold text-gray-700"><span class="w-3 h-3 rounded-full bg-emerald-500 mr-2"></span> Clear</div>
            <div class="flex items-center text-xs font-semibold text-gray-700"><span class="w-3 h-3 rounded-full bg-amber-500 mr-2"></span> Moderate</div>
            <div class="flex items-center text-xs font-semibold text-gray-700"><span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span> Flooded</div>
        </div>
    </div>

    <div class="absolute bottom-48 right-4 z-[500]">
        <button @click="locateMe()" class="bg-white text-gray-600 hover:text-blue-600 p-3 rounded-xl shadow-lg flex items-center justify-center">
            <svg x-show="loadingLocation" class="animate-spin h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <svg x-show="!loadingLocation" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
        </button>
    </div>
    <div class="absolute bottom-32 right-4 z-[500]">
        <button @click="resetMap()" class="bg-emerald-500 hover:bg-emerald-600 text-white p-3 rounded-xl shadow-lg"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg></button>
    </div>

    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl shadow-[0_-10px_40px_rgba(0,0,0,0.15)] z-[600] transition-transform duration-300 ease-out transform h-[65vh]" :class="detailOpen ? 'translate-y-0' : 'translate-y-[calc(100%-110px)]'">
        <div class="w-full flex justify-center pt-4 pb-4 cursor-pointer" @click="detailOpen = !detailOpen">
            <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
        </div>

        <div class="px-6 pb-24 h-full overflow-y-auto">
            @if($selectedHotspot)
                <button wire:click="clearSelection" @click="detailOpen = false" class="text-xs text-emerald-600 font-bold mb-4 flex items-center hover:underline">← OVERVIEW</button>

                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-2xl font-bold text-gray-800 leading-tight">{{ $selectedHotspot->name }} <br> Hotspot</h2>
                    <div class="text-right">
                        <span class="block text-3xl font-black text-gray-800">{{ round($selectedHotspot->water_level_cm) }}cm</span>
                        <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Water Level</span>
                    </div>
                </div>

                @if($selectedHotspot->status === 'flooded')
                    <span class="inline-block px-3 py-1 bg-red-100 text-red-600 text-xs font-bold rounded-full mb-6">ⓧ IMPASSABLE</span>
                @elseif($selectedHotspot->status === 'moderate')
                    <span class="inline-block px-3 py-1 bg-amber-100 text-amber-600 text-xs font-bold rounded-full mb-6">⚠ CAUTION</span>
                @else
                    <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-600 text-xs font-bold rounded-full mb-6">✓ PASSABLE</span>
                @endif

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <div class="flex justify-between items-start mb-1">
                            <div class="text-blue-500 text-xl">💧</div>
                            <span class="text-[10px] text-gray-400">Past Hr</span>
                        </div>
                        <div class="text-lg font-bold text-gray-800">{{ $selectedHotspot->rainfall_mm_hr ?? 0 }}mm</div>
                        <div class="text-[10px] text-gray-400 uppercase mt-1">Prev: {{ $selectedHotspot->previous_rainfall_mm ?? 0 }}mm</div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <div class="text-purple-500 text-xl mb-1">📈</div>
                        <div class="text-lg font-bold text-gray-800">{{ $selectedHotspot->confidence_score ?? 0 }}%</div>
                        <div class="text-[10px] text-gray-400 uppercase">Data Quality</div>
                    </div>
                </div>

                <div class="bg-blue-50 p-4 rounded-2xl mb-6">
                    <h5 class="text-xs font-bold text-blue-800 uppercase mb-2">Location Profile</h5>
                    <div class="flex justify-between text-sm">
                        <span class="text-blue-600">Elevation:</span>
                        <span class="font-bold text-blue-900">{{ $selectedHotspot->elevation_m ?? 0 }}m</span>
                    </div>
                    <div class="flex justify-between text-sm mt-1">
                        <span class="text-blue-600">Drainage Rating:</span>
                        <span class="font-bold text-blue-900">{{ $selectedHotspot->drainage_level ?? 0 }}/10</span>
                    </div>
                </div>
            @else
                <div class="flex justify-between items-end mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-1">Live Flood Zones</h3>
                        <p class="text-sm text-gray-500">Active Hotspot Mapping</p>
                    </div>
                </div>
                <div class="space-y-3">
                    @foreach($hotspots as $spot)
                    <div wire:click="selectHotspot({{ $spot->id }})" @click="detailOpen = true" class="flex items-center justify-between p-4 border border-gray-100 rounded-2xl shadow-sm cursor-pointer hover:bg-gray-50 transition">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3" style="background-color: {{ $spot->status === 'flooded' ? '#FEE2E2' : ($spot->status === 'moderate' ? '#FEF3C7' : '#D1FAE5') }}">
                                <div class="w-3 h-3 rounded-full shadow-sm" style="background-color: {{ $spot->status === 'flooded' ? '#EF4444' : ($spot->status === 'moderate' ? '#F59E0B' : '#10B981') }}"></div>
                            </div>
                            <div>
                                <h5 class="text-sm font-bold text-gray-800">{{ $spot->name }}</h5>
                                <span class="text-xs text-gray-500">Level: {{ round($spot->water_level_cm) }}cm</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
