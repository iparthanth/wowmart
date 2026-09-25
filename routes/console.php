<?php

use Illuminate\Support\Facades\Schedule;

// API tokens expire after config('sanctum.expiration') minutes; this removes
// the dead rows. Needs the scheduler running (`php artisan schedule:work`
// locally, one cron entry in production).
Schedule::command('sanctum:prune-expired --hours=24')->daily();
