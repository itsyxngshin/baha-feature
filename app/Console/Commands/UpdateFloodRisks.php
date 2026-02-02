<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Hotspot;
use App\Services\FloodPredictionService;

class UpdateFloodRisks extends Command
{
    protected $signature = 'baha:predict';
    protected $description = 'Fetch live rainfall and predict flood risks';

    public function handle()
    {
        $service = new FloodPredictionService();
        $apiKey = env('OPENWEATHER_API_KEY');
        
        // Get all hotspots
        $hotspots = Hotspot::all();
        $this->info("Fetching live weather for " . $hotspots->count() . " zones...");

        foreach ($hotspots as $hotspot) {
            
            // 1. CALL OPENWEATHER API
            // We use the hotspot's specific Latitude/Longitude
            $url = "https://api.openweathermap.org/data/2.5/weather?lat={$hotspot->latitude}&lon={$hotspot->longitude}&appid={$apiKey}&units=metric";
            
            try {
                $response = Http::get($url);
                
                if ($response->successful()) {
                    $weatherData = $response->json();

                    // 2. EXTRACT RAINFALL (OpenWeather returns 'rain' object if raining)
                    // '1h' key contains rain volume for the last 1 hour in mm
                    $currentRainfall = $weatherData['rain']['1h'] ?? 0.0;

                    // 3. THE "SHIFT" LOGIC (Crucial for your model's 'Previous_Rainfall' feature)
                    // We take the value currently in the DB and move it to "Previous"
                    // BEFORE we overwrite it with the new data.
                    $hotspot->previous_rainfall_mm = $hotspot->rainfall_mm_hr;
                    $hotspot->rainfall_mm_hr = $currentRainfall;
                    
                    // Save input data first
                    $hotspot->save();

                    // 4. RUN PREDICTION
                    // Now the Python script will use this REAL rainfall data
                    $service->predict($hotspot);

                    $this->info("Updated {$hotspot->name}: Rain {$currentRainfall}mm (Prev: {$hotspot->previous_rainfall_mm}mm)");
                } else {
                    $this->error("API Error for {$hotspot->name}");
                }

            } catch (\Exception $e) {
                $this->error("Connection failed: " . $e->getMessage());
            }
        }

        $this->info('Real-time prediction cycle complete.');
    }
}