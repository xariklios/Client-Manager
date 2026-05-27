<?php

namespace App\Console\Commands;

use App\Mail\ChargeExpiredAdminNotice;
use App\Mail\OverdueChargeReminder;
use App\Mail\UpcomingChargeReminder;
use App\Models\Charge;
use App\Models\ChargeReminder;
use App\Models\EmailLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendClientChargeReminders extends Command
{
    protected $signature   = 'charges:remind-clients';
    protected $description = 'Send payment reminder emails to clients for upcoming and overdue recurring charges';

    // Days before due date to send upcoming reminders
    private array $upcomingOffsets = [60, 30, 15];

    public function handle(): void
    {
        $sent = 0;

        $charges = Charge::whereNotNull('recurring_charge_id')
            ->whereHas('recurringCharge', fn($q) => $q->where('notify_client', true)->where('active', true))
            ->whereHas('client', fn($q) => $q->whereNotNull('email'))
            ->unpaid()
            ->whereNotNull('due_date')
            ->with(['client', 'project', 'recurringCharge', 'reminders'])
            ->get();

        foreach ($charges as $charge) {
            $today       = now()->startOfDay();
            $due         = $charge->due_date->startOfDay();
            $diffDays    = (int) $today->diffInDays($due, false); // positive = future, negative = past
            $sentOffsets = $charge->reminders->pluck('days_offset')->all();

            if ($diffDays >= 0) {
                // Upcoming — check if today matches a reminder offset
                $daysUntil = $diffDays;

                foreach ($this->upcomingOffsets as $target) {
                    if ($daysUntil === $target && ! in_array(-$target, $sentOffsets, true)) {
                        Mail::to($charge->client->email)->send(new UpcomingChargeReminder($charge, $daysUntil));
                        ChargeReminder::create(['charge_id' => $charge->id, 'days_offset' => -$target, 'sent_at' => now()]);
                        EmailLog::create(['type' => 'upcoming_reminder', 'recipient' => $charge->client->email, 'subject' => 'Υπενθύμιση Ανανέωσης: ' . $charge->title, 'records_count' => 1]);
                        $this->info("Upcoming reminder ({$daysUntil}d) → {$charge->client->email} for '{$charge->title}'");
                        $sent++;
                    }
                }
            } else {
                // Overdue — send every 3 days while unpaid
                $daysOverdue = abs($diffDays);
                $slot        = (int) ceil($daysOverdue / 3) * 3; // rounds up to nearest 3: day 1→3, 3→3, 4→6...

                // Only send on exact multiples of 3 (day 3, 6, 9...)
                if ($daysOverdue % 3 === 0 && $daysOverdue > 0 && ! in_array($daysOverdue, $sentOffsets, true)) {
                    Mail::to($charge->client->email)->send(new OverdueChargeReminder($charge, $daysOverdue));
                    ChargeReminder::create(['charge_id' => $charge->id, 'days_offset' => $daysOverdue, 'sent_at' => now()]);
                    EmailLog::create(['type' => 'overdue_reminder', 'recipient' => $charge->client->email, 'subject' => 'Ληξιπρόθεσμη Πληρωμή: ' . $charge->title, 'records_count' => 1]);
                    $this->info("Overdue reminder ({$daysOverdue}d late) → {$charge->client->email} for '{$charge->title}'");
                    $sent++;
                }
            }
        }

        // Admin notification: all unpaid charges due today
        $dueToday = Charge::unpaid()
            ->whereDate('due_date', today())
            ->with(['client', 'project', 'reminders'])
            ->get();

        foreach ($dueToday as $charge) {
            if ($charge->reminders->where('days_offset', 0)->isEmpty()) {
                Mail::to(config('app.admin_email'))->send(new ChargeExpiredAdminNotice($charge));
                ChargeReminder::create(['charge_id' => $charge->id, 'days_offset' => 0, 'sent_at' => now()]);
                EmailLog::create(['type' => 'expiry_notice', 'recipient' => config('app.admin_email'), 'subject' => 'Λήξη υπηρεσίας: ' . $charge->title . ' — ' . $charge->client->name, 'records_count' => 1]);
                $this->info("Admin expiry notice → {$charge->client->name} / '{$charge->title}'");
                $sent++;
            }
        }

        $this->info("Done. {$sent} reminder(s) sent.");
    }
}
