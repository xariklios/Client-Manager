<?php

namespace App\Console\Commands;

use App\Mail\UpcomingRenewalsDigest;
use App\Models\EmailLog;
use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendRenewalReminders extends Command
{
    protected $signature   = 'reminders:renewals {--days=30 : How many days ahead to look}';
    protected $description = 'Send a digest of upcoming project renewals to the admin';

    public function handle(): void
    {
        $days = (int) $this->option('days');

        $projects = Project::with('client')
            ->where('status', 'active')
            ->whereNotNull('renewal_date')
            ->whereBetween('renewal_date', [now()->toDateString(), now()->addDays($days)->toDateString()])
            ->orderBy('renewal_date')
            ->get();

        if ($projects->isEmpty()) {
            $this->info("No renewals in the next {$days} days. Nothing sent.");
            return;
        }

        $recipient = config('app.admin_email');
        $subject   = "[Client Manager] {$projects->count()} renewal(s) in the next {$days} days";

        Mail::to($recipient)->send(new UpcomingRenewalsDigest($projects, $days));

        EmailLog::create([
            'type'          => 'upcoming_renewals',
            'recipient'     => $recipient,
            'subject'       => $subject,
            'records_count' => $projects->count(),
        ]);

        $this->info("Sent renewals digest to {$recipient} ({$projects->count()} projects).");
    }
}
