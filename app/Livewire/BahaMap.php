<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Hotspot;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class BahaMap extends Component
{
    // 1. STRONGLY TYPE the model so Livewire knows how to hydrate it.
    // Notice the "?Hotspot" (which means it can be a Hotspot model or null).
    public ?Hotspot $selectedHotspot = null;

    public $searchQuery = '';

    // Notice we completely REMOVED public $hotspots and the mount() function.
    // This stops Livewire from sending massive data payloads back and forth!

    public function selectHotspot($id)
    {
        // Fetches the data from the database and assigns it to the typed property
        $this->selectedHotspot = Hotspot::find($id);
    }

    public function clearSelection()
    {
        $this->selectedHotspot = null;
    }

    public function render()
    {
        return view('livewire.baha-map', [
            // 2. PASS HOTSPOTS DIRECTLY HERE.
            // This renders the map markers perfectly without bloating the Livewire state.
            'hotspots' => Hotspot::all(),

            'filteredHotspots' => strlen($this->searchQuery) > 0
                ? Hotspot::where('name', 'like', '%'.$this->searchQuery.'%')->get()
                : []
        ]);
    }
}
