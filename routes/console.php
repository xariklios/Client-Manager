<?php

use Illuminate\Support\Facades\Schedule;

// Admin digests
Schedule::command('reminders:overdue')->dailyAt('08:00');
Schedule::command('reminders:renewals')->weeklyOn(1, '08:00');

// Recurring charges: generate Charge records 60 days before due date
Schedule::command('charges:generate')->dailyAt('07:00');

// Client-facing payment reminders (upcoming + overdue)
Schedule::command('charges:remind-clients')->dailyAt('09:00');
