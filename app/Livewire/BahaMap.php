<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Hotspot;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class BahaMap extends Component
{
    // Must be strongly typed so Livewire can fetch the data automatically
    public ?Hotspot $selectedHotspot = null;

    public $searchQuery = '';

    public function selectHotspot($id)
    {
        $this->selectedHotspot = Hotspot::find($id);
    }

    public function clearSelection()
    {
        $this->selectedHotspot = null;
    }

    public function render()
    {
        return view('livewire.baha-map', [
            'hotspots' => Hotspot::all(),
            'filteredHotspots' => strlen($this->searchQuery) > 0
                ? Hotspot::where('name', 'like', '%'.$this->searchQuery.'%')->get()
                : []
        ]);
    }
}
