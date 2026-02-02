<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;
use App\Models\Hotspot;

class FloodPredictionService
{
    public function predict(Hotspot $hotspot)
    {
        $scriptPath = storage_path('app/models/predict_flood.py');
        $modelPath = storage_path('app/models/baha_flood_model.pkl');

        // Prepare data payload
        $data = json_encode([
            'rainfall'      => $hotspot->rainfall_mm_hr,
            'prev_rainfall' => $hotspot->previous_rainfall_mm,
            'elevation'     => $hotspot->elevation_m,
            'drainage'      => $hotspot->drainage_level,
        ]);

        // Execute Python
        $result = Process::run("python3 {$scriptPath} {$modelPath} '{$data}'");

        if ($result->failed()) {
            \Log::error("ML Error: " . $result->errorOutput());
            return;
        }

        $output = json_decode($result->output(), true);

        if ($output['status'] === 'success') {
            $level = $output['water_level'];
            
            // Save results back to DB
            $hotspot->update([
                'water_level_cm' => $level,
                'status'         => $this->calculateRisk($level),
                'confidence_score' => 94 // Placeholder or derived
            ]);
        }
    }
    

    private function calculateRisk($level)
    {
        if ($level <= 10) return 'clear';
        if ($level <= 30) return 'moderate';
        return 'flooded';
    }
}