<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use App\Models\Hotspot;

class FloodPredictionService
{
    /**
     * Run the Python ML model and calculate confidence.
     */
    public function predict(Hotspot $hotspot): void
    {
        $scriptPath = storage_path('app/models/predict_flood.py');
        $modelPath = storage_path('app/models/baha_flood_model.pkl');

        // 1. Prepare Data
        $inputData = json_encode([
            'rainfall'      => $hotspot->rainfall_mm_hr,
            'prev_rainfall' => $hotspot->previous_rainfall_mm,
            'elevation'     => $hotspot->elevation_m,
            'drainage'      => $hotspot->drainage_level,
        ]);

        // 2. Run Python
        $result = Process::run("python3 {$scriptPath} {$modelPath} '{$inputData}'");

        if ($result->failed()) {
            Log::error("Prediction Failed for {$hotspot->name}: " . $result->errorOutput());
            return;
        }

        $output = json_decode($result->output(), true);

        if (isset($output['status']) && $output['status'] === 'success') {
            $waterLevel = $output['water_level'];

            // 3. CALCULATE CONFIDENCE (The New Logic)
            $confidence = $this->calculateHeuristicConfidence($hotspot->rainfall_mm_hr, $waterLevel);

            // 4. Update Database
            $hotspot->update([
                'water_level_cm'   => $waterLevel,
                'status'           => $this->determineRiskLevel($waterLevel),
                'confidence_score' => $confidence 
            ]);

            Log::info("Updated {$hotspot->name}: Level {$waterLevel}cm (Conf: {$confidence}%)");
        }
    }

    /**
     * Generate a confidence score based on input stability.
     */
    private function calculateHeuristicConfidence($rainfall, $waterLevel): int
    {
        // RULE 1: If it's not raining, we are 99% sure it's not flooding.
        if ($rainfall == 0 && $waterLevel < 5) {
            return 99;
        }

        // RULE 2: Start with a high baseline for standard conditions.
        $score = 94;

        // RULE 3: Penalize for extreme weather (Linear models struggle here).
        if ($rainfall > 50) {
            $score -= 15; // Heavy storm -> Lower confidence (~79%)
        } elseif ($rainfall > 20) {
            $score -= 5;  // Moderate rain -> Slight penalty (~89%)
        }

        // RULE 4: Sanity Check (Negative water level = model failure).
        if ($waterLevel < 0) {
            return 0; 
        }

        return $score;
    }

    private function determineRiskLevel(float $level): string
    {
        if ($level <= 15) return 'clear';
        if ($level <= 40) return 'moderate';
        return 'flooded';
    }
}