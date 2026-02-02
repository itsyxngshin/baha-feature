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
            'elevation_m' => 'required|numeric', // Critical for ML model
            'drainage_level' => 'required|integer|min:1|max:10',
        ]);

        Hotspot::create([
            'name' => $this->name, 
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'elevation_m' => $this->elevation_m,
            'drainage_level' => $this->drainage_level,
            // Default values for dynamic fields
            'rainfall_mm_hr' => 0,
            'previous_rainfall_mm' => 0,
            'water_level_cm' => 0,
            'status' => 'clear'
        ]);

        $this->reset();
        session()->flash('message', 'New road added successfully!');
        
        // Optional: Trigger a recalculation immediately
        // \Illuminate\Support\Facades\Artisan::call('baha:predict');
    }

    public function render()
    {
        return view('livewire.admin.add-hotspot');
    }
}