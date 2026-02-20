<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Hotspot;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class BahaMap extends Component
{
    public ?Hotspot $selectedHotspot = null;

    public $searchQuery = '';


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
            'hotspots' => Hotspot::all(),

            'filteredHotspots' => strlen($this->searchQuery) > 0
                ? Hotspot::where('name', 'like', '%'.$this->searchQuery.'%')->get()
                : []
        ]);
    }
}
