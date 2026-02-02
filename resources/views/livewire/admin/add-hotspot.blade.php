<div class="min-h-screen bg-gray-100 flex flex-col items-center justify-center p-6" x-data="adminMap()">
    
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl overflow-hidden flex flex-col md:flex-row h-[80vh]">
        
        <div class="w-full md:w-1/3 p-8 flex flex-col justify-center bg-white z-10 relative">
            <h2 class="text-2xl font-bold text-gray-800 mb-1">Add New Road</h2>
            <p class="text-xs text-gray-500 mb-6">Click on the map to set location.</p>

            @if (session()->has('message'))
                <div class="bg-emerald-100 text-emerald-600 p-3 rounded-lg text-xs font-bold mb-4">
                    {{ session('message') }}
                </div>
            @endif

            <form wire:submit.prevent="save" class="space-y-4">
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Road / Zone Name</label>
                    <input wire:model="name" type="text" placeholder="e.g. Roxas Avenue (North)" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-emerald-500 transition">
                    @error('name') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                </div>

                <div>
                    <div class="flex justify-between">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Drainage Quality</label>
                        <span class="text-xs font-bold text-emerald-600" x-text="$wire.drainage_level + '/10'"></span>
                    </div>
                    <input wire:model.live="drainage_level" type="range" min="1" max="10" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-emerald-500">
                    <div class="flex justify-between text-[10px] text-gray-400 mt-1">
                        <span>Poor (Floods fast)</span>
                        <span>Excellent</span>
                    </div>
                    @error('drainage_level') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase">Latitude</label>
                        <input wire:model="latitude" type="text" readonly class="w-full bg-gray-100 text-gray-500 rounded px-2 py-1 text-xs">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase">Longitude</label>
                        <input wire:model="longitude" type="text" readonly class="w-full bg-gray-100 text-gray-500 rounded px-2 py-1 text-xs">
                    </div>
                </div>
                @error('latitude') <span class="text-red-500 text-[10px]">Please click map to set location</span> @enderror

                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl shadow-lg transition transform hover:scale-[1.02] mt-4">
                    Save Location
                </button>
            </form>

            <a href="{{ route('baha.map') }}" class="block text-center text-xs text-gray-400 mt-6 hover:underline">
                ← Back to Main Map
            </a>
        </div>

        <div class="w-full md:w-2/3 relative">
            <div id="admin-map" wire:ignore class="h-full w-full"></div>
            
            <div class="absolute top-4 right-4 bg-white/90 backdrop-blur px-3 py-2 rounded-lg shadow-sm z-[500] text-xs font-medium text-gray-600">
                📍 Click anywhere to drop a pin
            </div>
        </div>

    </div>
</div>

<script>
    function adminMap() {
        return {
            map: null,
            marker: null,
            init() {
                // Initialize Map centered on Naga
                this.map = L.map('admin-map').setView([13.621775, 123.194830], 14);

                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                    attribution: '&copy; OpenStreetMap',
                    maxZoom: 20
                }).addTo(this.map);

                // Existing markers (optional: show existing spots so you don't duplicate)
                // You can pass existing spots via Livewire if needed

                // CLICK EVENT: Add Pin & Update Livewire
                this.map.on('click', (e) => {
                    const lat = e.latlng.lat.toFixed(6);
                    const lng = e.latlng.lng.toFixed(6);

                    // Update Livewire properties
                    this.$wire.set('latitude', lat);
                    this.$wire.set('longitude', lng);

                    // Move or Create Marker
                    if (this.marker) {
                        this.marker.setLatLng(e.latlng);
                    } else {
                        this.marker = L.marker(e.latlng, { draggable: true }).addTo(this.map);
                        
                        // Update on drag end
                        this.marker.on('dragend', (event) => {
                            const pos = event.target.getLatLng();
                            this.$wire.set('latitude', pos.lat.toFixed(6));
                            this.$wire.set('longitude', pos.lng.toFixed(6));
                        });
                    }
                });
            }
        }
    }
</script>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('road-saved', () => {
            // 1. Show Success Toast
            Swal.fire({
                icon: 'success',
                title: 'Road Added!',
                text: 'Elevation data will be auto-fetched.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#ECFDF5', // Emerald-50
                color: '#065F46' // Emerald-800
            });

            // 2. Clear the Map Pin (Visual Reset)
            // We need to access the Alpine component scope to remove the marker
            // A simple page reload is often easiest, but for SPA feel:
            const mapComponent = document.querySelector('[x-data]').__x.$data;
            if(mapComponent.marker) {
                mapComponent.map.removeLayer(mapComponent.marker);
                mapComponent.marker = null;
            }
        });
    });
</script>