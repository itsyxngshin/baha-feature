<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Hotspot;

class BahaMap extends Component
{
    public $hotspots;
    public $selectedHotspot = null;
    public $searchQuery = '';

    public function mount()
    {
        $this->hotspots = Hotspot::all();
    }

    // Called when a user clicks a marker on the map (via JS)
    public function selectHotspot($id)
    {
        $this->selectedHotspot = Hotspot::find($id);
    }

    // Called to close the bottom sheet
    public function clearSelection()
    {
        $this->selectedHotspot = null;
    }

    public function render()
    {
        return view('livewire.baha-map', [
            // Simple search filter
            'filteredHotspots' => Hotspot::where('name', 'like', '%'.$this->searchQuery.'%')->get()
        ]);
    }
}