<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use App\Console\Commands\ListenToAllRedisChannels;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Artisan::command('redis:listen', function () {
    (new ListenToAllRedisChannels())->handle();
    
})->purpose('Display an inspiring quote')->hourly();






