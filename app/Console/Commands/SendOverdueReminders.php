<?php

namespace App\Console\Commands;

use App\Mail\OverdueChargesDigest;
use App\Models\Charge;
use App\Models\EmailLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendOverdueReminders extends Command
{
    protected $signature   = 'reminders:overdue';
    protected $description = 'Send a digest of overdue charges to the admin';

    public function handle(): void
    {
        $charges = Charge::overdue()->with(['client', 'project'])->get();

        if ($charges->isEmpty()) {
            $this->info('No overdue charges. Nothing sent.');
            return;
        }

        $recipient = config('app.admin_email');
        $subject   = "[Client Manager] {$charges->count()} overdue charge(s)";

        Mail::to($recipient)->send(new OverdueChargesDigest($charges));

        EmailLog::create([
            'type'          => 'overdue_charges',
            'recipient'     => $recipient,
            'subject'       => $subject,
            'records_count' => $charges->count(),
        ]);

        $this->info("Sent overdue digest to {$recipient} ({$charges->count()} charges).");
    }
}
