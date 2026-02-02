<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model; 

class Hotspot extends Model 
{
    protected $guarded = [];

    // Helper to get color code based on status for the UI
    public function getColorAttribute()
    {
        return match($this->status) {
            'clear' => '#10B981', // Emerald-500
            'moderate' => '#F59E0B', // Amber-500
            'flooded' => '#EF4444', // Red-500
            default => '#9CA3AF',
        };
    }
}
