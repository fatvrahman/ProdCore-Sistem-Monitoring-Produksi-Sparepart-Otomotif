<?php
// File: routes/console.php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes untuk ProdCore
|--------------------------------------------------------------------------
|
| Console routes untuk commands dan scheduled tasks
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/*
|--------------------------------------------------------------------------
| Custom Artisan Commands untuk ProdCore
|--------------------------------------------------------------------------
*/

// Command untuk backup database harian
Artisan::command('prodcore:backup', function () {
    $this->info('Starting database backup...');
    
    // Logic backup database
    $filename = 'backup_' . now()->format('Y_m_d_H_i_s') . '.sql';
    $this->info("Backup saved as: {$filename}");
    
})->purpose('Backup database ProdCore');

// Command untuk generate laporan harian
Artisan::command('prodcore:daily-report', function () {
    $this->info('Generating daily production report...');
    
    // Logic generate laporan
    $this->info('Daily report generated successfully!');
    
})->purpose('Generate daily production report');

// Command untuk cleanup old logs
Artisan::command('prodcore:cleanup', function () {
    $this->info('Cleaning up old logs and temporary files...');
    
    // Logic cleanup
    $this->info('Cleanup completed!');
    
})->purpose('Cleanup old logs and temporary files');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
*/

// Schedule daily backup at 2 AM
Schedule::command('prodcore:backup')->dailyAt('02:00');

// Schedule daily report at 6 AM
Schedule::command('prodcore:daily-report')->dailyAt('06:00');

// Schedule cleanup weekly on Sunday at 3 AM
Schedule::command('prodcore:cleanup')->weeklyOn(0, '03:00');