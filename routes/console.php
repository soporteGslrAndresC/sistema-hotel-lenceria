<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cada 30 minutos: alertar habitaciones en limpieza > 2h
Schedule::command('app:revisar-habitaciones-lentas')->everyThirtyMinutes();
