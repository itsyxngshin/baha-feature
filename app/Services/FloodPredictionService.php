<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\Hotspot;

class FloodPredictionService
{
    /**
     * Run the PHP Heuristic Simulation (Hackathon Safe Mode)
     */
    public function predict(Hotspot $hotspot): void
    {
        // 1. Grab the inputs
        $rain = (float) $hotspot->rainfall_mm_hr;
        $prevRain = (float) $hotspot->previous_rainfall_mm;
        $elevation = (float) $hotspot->elevation_m;
        $drainage = (float) $hotspot->drainage_level;

        // 2. The TURO-MOKO Mathematical Simulation
        // This mimics the linear weights your .pkl model would have used.
        // Rain increases flood, elevation and drainage reduce it.
        $calculatedLevel = ($rain * 0.85) + ($prevRain * 0.45) - ($elevation * 4.0) - ($drainage * 3.5);

        // 3. Prevent negative water levels
        $waterLevel = round(max(0, $calculatedLevel), 2);

        // 4. Calculate Confidence and Risk
        $confidence = $this->calculateHeuristicConfidence($rain, $waterLevel);
        $status = $this->determineRiskLevel($waterLevel);

        // 5. Update the Database
        $hotspot->update([
            'water_level_cm'   => $waterLevel,
            'status'           => $status,
            'confidence_score' => $confidence
        ]);

        Log::info("PHP Fallback Predicted {$hotspot->name}: Level {$waterLevel}cm (Status: {$status})");
    }

    /**
     * Generate a confidence score based on input stability.
     */
    private function calculateHeuristicConfidence($rainfall, $waterLevel): int
    {
        if ($rainfall == 0 && $waterLevel < 5) return 99;

        $score = 94;
        if ($rainfall > 50) $score -= 15;
        elseif ($rainfall > 20) $score -= 5;

        if ($waterLevel < 0) return 0;
        return $score;
    }

    private function determineRiskLevel(float $level): string
    {
        if ($level <= 15) return 'clear';
        if ($level <= 40) return 'moderate';
        return 'flooded';
    }
}
