<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Hotspot extends Model
{
    protected $guarded = [];

    /**
     * The "booted" method of the model.
     * Automatically triggers when a new Hotspot is being created.
     */
    protected static function booted(): void
    {
        static::creating(function (Hotspot $hotspot) {
            
            // Only fetch if elevation is missing or explicitly 0
            if (empty($hotspot->elevation_m)) {
                
                try {
                    // API Endpoint (Open-Elevation - Free)
                    $url = 'https://api.open-elevation.com/api/v1/lookup';
                    
                    $response = Http::get($url, [
                        'locations' => "{$hotspot->latitude},{$hotspot->longitude}"
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        
                        // Extract elevation from response
                        // Response format: { "results": [ { "elevation": 10, ... } ] }
                        $elevation = $data['results'][0]['elevation'] ?? null;

                        if ($elevation !== null) {
                            $hotspot->elevation_m = $elevation;
                            Log::info("Auto-fetched Elevation for {$hotspot->name}: {$elevation}m");
                        }
                    }
                } catch (\Exception $e) {
                    // Fallback if API is down: Log it and set a safe default (e.g., 5m)
                    Log::error("Elevation API failed for {$hotspot->name}: " . $e->getMessage());
                    $hotspot->elevation_m = 5.0; // Default fallback
                }
            }
        });
    }

    // ... existing getColorAttribute code ...
    public function getColorAttribute()
    {
        return match($this->status) {
            'flooded' => '#EF4444', 
            'moderate' => '#F59E0B',
            'clear' => '#10B981',   
            default => '#9CA3AF',
        };
    }
}