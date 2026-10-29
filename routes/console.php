<?php

use Illuminate\Support\Facades\Schedule;

// Automatically purge dead sessions every single day at 03:15 AM
Schedule::command('lapak:pangkas-sesi')->dailyAt('03:15');
