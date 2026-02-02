<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspots', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->decimal('latitude', 10, 8);  // Latitude max is 90 (2 digits), so 10,8 is fine
            $table->decimal('longitude', 11, 8); // Longitude max is 180 (3 digits), so 11,8 is required
            
            // --- ML Model Inputs (Features) ---
            // These must match the columns your .pkl model expects
            $table->float('rainfall_mm_hr')->default(0);       // Variable
            $table->float('previous_rainfall_mm')->default(0); // Variable
            $table->float('elevation_m')->default(0);          // Constant (Location specific)
            $table->integer('drainage_level')->default(5);     // Constant (1-10 scale)
            
            // --- ML Model Outputs (Predictions) ---
            $table->float('water_level_cm')->default(0);       // Predicted Result
            $table->string('status')->default('clear');        // Derived (clear/moderate/flooded)
            $table->integer('confidence_score')->default(0);   // Simulated or Model derived
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotspots');
    }
};