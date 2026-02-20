<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\SimulateFlood;

// Run the prediction command every hour
Schedule::command('baha:predict')->hourly();
Artisan::starting(function ($artisan) {
    $artisan->resolve(SimulateFlood::class);
});
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
