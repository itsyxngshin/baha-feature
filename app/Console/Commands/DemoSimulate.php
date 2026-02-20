<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Hotspot;
use App\Services\FloodPredictionService;

class SimulateFlood extends Command
{
    protected $signature = 'baha:simulate {intensity=heavy}';
    protected $description = 'Inject synthetic rainfall to simulate flooding';

    public function handle()
    {
        $service = new FloodPredictionService();
        $intensity = $this->argument('intensity');

        // Define synthetic scenarios (mm/hr)
        $scenarios = [
            'light' => 2.5,
            'moderate' => 15.0,
            'heavy' => 55.0, // This will likely trigger "Flooded" status
        ];

        $rainValue = $scenarios[$intensity] ?? 55.0;
        $hotspots = Hotspot::all();

        $this->info("Simulating $intensity rain ($rainValue mm/hr) across all zones...");

        foreach ($hotspots as $hotspot) {
            // Shift current to previous to simulate a continuous storm
            $hotspot->previous_rainfall_mm = $hotspot->rainfall_mm_hr;
            $hotspot->rainfall_mm_hr = $rainValue;
            $hotspot->save();

            // Trigger your Python prediction logic
            $service->predict($hotspot); 
            
            $this->line("Updated {$hotspot->name}: Status is now {$hotspot->status}");
        }

        $this->info('Simulation complete. Check the map!');
    }
}
