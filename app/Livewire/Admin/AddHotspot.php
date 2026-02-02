<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Hotspot;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class AddHotspot extends Component
{
    public $name, $latitude, $longitude, $elevation_m, $drainage_level;

    public function save()
    {
        $this->validate([
            'name' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'drainage_level' => 'required|integer|min:1|max:10',
        ]);

        Hotspot::create([
            'name' => $this->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'drainage_level' => $this->drainage_level,
            'rainfall_mm_hr' => 0,
            'previous_rainfall_mm' => 0,
            'water_level_cm' => 0,
            'status' => 'clear'
        ]);

        $this->reset(); 

        // TRIGGER THE TOAST
        $this->dispatch('road-saved'); 
    }

    public function render()
    {
        return view('livewire.admin.add-hotspot');
    }
}