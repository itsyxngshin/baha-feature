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
        $pythonCmd = '/usr/bin/python3';

        // 1. Run Process using DIRECT ARGUMENTS (No JSON to break on Windows)
        $result = Process::run([
            $pythonCmd,
            $scriptPath,
            $modelPath,
            (float) $hotspot->rainfall_mm_hr,
            (float) $hotspot->previous_rainfall_mm,
            (float) $hotspot->elevation_m,
            (float) $hotspot->drainage_level
        ]);

        // 2. Check for outright OS/Execution failures
        if ($result->failed()) {
            Log::error("OS Python Error for {$hotspot->name}: " . $result->errorOutput());
            return;
        }

        $output = json_decode($result->output(), true);

        // 3. NEW: Catch Python's internal errors (e.g., missing features, model errors)
        if (isset($output['status']) && $output['status'] === 'error') {
            Log::error("ML Model Error for {$hotspot->name}: " . $output['message']);
            return;
        }

        // 4. Handle Success
        if (isset($output['status']) && $output['status'] === 'success') {
            $waterLevel = $output['water_level'];

            // Calculate Confidence
            $confidence = $this->calculateHeuristicConfidence($hotspot->rainfall_mm_hr, $waterLevel);

            // Update Database
            $hotspot->update([
                'water_level_cm'   => $waterLevel,
                'status'           => $this->determineRiskLevel($waterLevel),
                'confidence_score' => $confidence
            ]);

            Log::info("Updated {$hotspot->name}: Level {$waterLevel}cm (Conf: {$confidence}%)");
        } else {
            // Catch anything weird that Python might have printed (like warnings)
            Log::warning("Unexpected Output for {$hotspot->name}: " . $result->output());
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
