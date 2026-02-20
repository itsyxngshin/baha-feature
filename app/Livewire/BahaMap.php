<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Hotspot;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class BahaMap extends Component
{
    public $selectedHotspotId = null; // Store ID to avoid serialization issues
    public $searchQuery = '';

    public function selectHotspot($id)
    {
        $this->selectedHotspotId = $id;
    }

    public function clearSelection()
    {
        $this->selectedHotspotId = null;
    }

    public function render()
    {
        return view('livewire.baha-map', [
            'hotspots' => Hotspot::all(),
            'selectedHotspot' => $this->selectedHotspotId ? Hotspot::find($this->selectedHotspotId) : null,
            'filteredHotspots' => strlen($this->searchQuery) > 0
                ? Hotspot::where('name', 'like', '%'.$this->searchQuery.'%')->get()
                : []
        ]);
    }
}
