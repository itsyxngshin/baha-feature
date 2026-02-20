<div class="min-h-screen bg-gray-100 flex flex-col items-center justify-center p-6" x-data="adminMap()">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl overflow-hidden flex flex-col md:flex-row h-[80vh]">

        <div class="w-full md:w-1/3 p-8 flex flex-col justify-center bg-white z-10 relative">
            <h2 class="text-2xl font-bold text-gray-800 mb-1">Add New Road</h2>
            <p class="text-xs text-gray-500 mb-6">Click on the map to set location.</p>

            <form wire:submit.prevent="save" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Road / Zone Name</label>
                    <div class="relative">
                        <input
                            wire:model="name"
                            type="text"
                            :placeholder="isFetching ? 'Locating road...' : 'e.g. Roxas Avenue (North)'"
                            :disabled="isFetching"
                            class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-emerald-500 transition disabled:opacity-50"
                        >
                        <div x-show="isFetching" class="absolute right-3 top-2.5">
                            <svg class="animate-spin h-5 w-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                    @error('name') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                </div>

                <div>
                    <div class="flex justify-between">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Drainage Quality</label>
                        <span class="text-xs font-bold text-emerald-600" x-text="$wire.drainage_level + '/10'"></span>
                    </div>
                    <input wire:model.live="drainage_level" type="range" min="1" max="10" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-emerald-500">
                    @error('drainage_level') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase">Latitude</label>
                        <input wire:model="latitude" type="text" readonly class="w-full bg-gray-100 text-gray-500 rounded px-2 py-1 text-xs outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase">Longitude</label>
                        <input wire:model="longitude" type="text" readonly class="w-full bg-gray-100 text-gray-500 rounded px-2 py-1 text-xs outline-none">
                    </div>
                </div>

                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl shadow-lg transition transform hover:scale-[1.02] mt-4">
                    Save Location
                </button>
            </form>
        </div>

        <div class="w-full md:w-2/3 relative">
            <div id="admin-map" wire:ignore class="h-full w-full z-0"></div>
            <div class="absolute top-4 right-4 bg-white/90 backdrop-blur px-3 py-2 rounded-lg shadow-sm z-[500] text-xs font-medium text-gray-600">
                📍 Click anywhere to drop a pin
            </div>
        </div>
    </div>

    <script>
        function adminMap() {
            return {
                map: null,
                marker: null,
                isFetching: false,
                searchTimeout: null,

                init() {
                    setTimeout(() => {
                        this.map = L.map('admin-map').setView([13.621775, 123.194830], 15);
                        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                            maxZoom: 20,
                            attribution: '© OpenStreetMap'
                        }).addTo(this.map);

                        this.map.on('click', (e) => {
                            this.updatePosition(e.latlng.lat, e.latlng.lng);
                        });
                    }, 200);
                },

                updatePosition(lat, lng) {
                    const formattedLat = parseFloat(lat).toFixed(6);
                    const formattedLng = parseFloat(lng).toFixed(6);

                    // Update Marker
                    if (this.marker) {
                        this.marker.setLatLng([formattedLat, formattedLng]);
                    } else {
                        const customPin = L.divIcon({
                            className: '!bg-transparent !border-0',
                            html: `<div class="relative flex items-center justify-center -mt-8"><svg class="w-10 h-10 text-emerald-600 drop-shadow-lg" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg></div>`,
                            iconSize: [40, 40],
                            iconAnchor: [20, 40]
                        });
                        this.marker = L.marker([formattedLat, formattedLng], { icon: customPin, draggable: true }).addTo(this.map);

                        this.marker.on('dragend', (event) => {
                            const pos = event.target.getLatLng();
                            this.updatePosition(pos.lat, pos.lng);
                        });
                    }

                    // Update Livewire
                    @this.set('latitude', formattedLat);
                    @this.set('longitude', formattedLng);

                    // Fetch Road Name with Debounce
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        this.fetchRoadName(formattedLat, formattedLng);
                    }, 600);
                },

                async fetchRoadName(lat, lng) {
                    this.isFetching = true;
                    try {
                        const response = await fetch(
                            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`,
                            { headers: { 'Accept-Language': 'en' } }
                        );
                        const data = await response.json();

                        if (data && data.address) {
                            const a = data.address;
                            // Priority sequence for road names
                            const road = a.road || a.highway || a.pedestrian || a.street || a.path || "Unnamed Road";
                            const area = a.suburb || a.neighbourhood || a.village || "";

                            const displayValue = area ? `${road}, ${area}` : road;
                            @this.set('name', displayValue);
                        }
                    } catch (error) {
                        console.error("Geocoding failed", error);
                    } finally {
                        this.isFetching = false;
                    }
                }
            }
        }

        document.addEventListener('livewire:initialized', () => {
            Livewire.on('road-saved', () => {
                Swal.fire({
                    icon: 'success', title: 'Location Saved', toast: true, position: 'top-end',
                    showConfirmButton: false, timer: 3000
                });

                // Reset Map Marker
                const alpine = document.querySelector('[x-data]').__x.$data;
                if(alpine.marker) {
                    alpine.map.removeLayer(alpine.marker);
                    alpine.marker = null;
                }
            });
        });
    </script>
</div>
