<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Hotspot;
use App\Services\FloodPredictionService;

class UpdateFloodRisks extends Command
{
    protected $signature = 'baha:predict';
    protected $description = 'Recalculate flood risks based on current rainfall';

    public function handle()
    {
        $service = new FloodPredictionService();
        $hotspots = Hotspot::all();

        $this->info("Updating " . $hotspots->count() . " zones...");

        foreach ($hotspots as $hotspot) {
            // OPTIONAL: Fetch live weather API here to update $hotspot->rainfall_mm_hr
            
            $service->predict($hotspot);
            $this->info("Updated: {$hotspot->name}");
        }
    }
}