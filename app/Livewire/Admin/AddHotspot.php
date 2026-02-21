<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Hotspot;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\FloodPredictionService;

#[Layout('layouts.app')]
class AddHotspot extends Component
{
    public $name, $latitude, $longitude, $drainage_level = 5;

    public function save(FloodPredictionService $predictionService)
    {
        $this->validate([
            'name' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'drainage_level' => 'required|integer|min:1|max:10',
        ]);

        // 1. Fetch Initial Rainfall Data
        $currentRainfall = 0.0;
        $apiKey = env('OPENWEATHER_API_KEY');

        if ($apiKey) {
            try {
                $url = "https://api.openweathermap.org/data/2.5/weather?lat={$this->latitude}&lon={$this->longitude}&appid={$apiKey}&units=metric";
                $response = Http::timeout(5)->get($url);

                if ($response->successful()) {
                    $weatherData = $response->json();
                    // OpenWeather returns 'rain.1h' if it's currently raining
                    $currentRainfall = $weatherData['rain']['1h'] ?? 0.0;
                }
            } catch (\Exception $e) {
                Log::warning("Could not fetch initial weather for new hotspot: " . $e->getMessage());
            }
        }

        // 2. Create the Hotspot with fetched data
        $hotspot = Hotspot::create([
            'name' => $this->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'drainage_level' => $this->drainage_level,
            'rainfall_mm_hr' => $currentRainfall,
            'previous_rainfall_mm' => 0, // No previous data on creation
            'water_level_cm' => 0,       // Default, will be updated by Python
            'status' => 'clear'          // Default
        ]);

        // 3. Run the Prediction immediately to get the water_level and correct status
        $predictionService->predict($hotspot);

        // 4. Reset Form and Dispatch Event
        $this->reset(['name', 'latitude', 'longitude']);
        $this->drainage_level = 5;

        $this->dispatch('road-saved');
    }

    public function render()
    {
        return view('livewire.admin.add-hotspot');
    }
}
