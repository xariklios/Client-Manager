<?php

namespace App\Console\Commands;

use App\Enums\ChargeStatus;
use App\Models\Charge;
use App\Models\RecurringCharge;
use Illuminate\Console\Command;

class GenerateRecurringCharges extends Command
{
    protected $signature   = 'charges:generate';
    protected $description = 'Generate Charge records from active recurring charges (60 days before due date)';

    public function handle(): void
    {
        $generated = 0;

        RecurringCharge::where('active', true)->with('client')->get()->each(function (RecurringCharge $recurring) use (&$generated) {
            $dueDate = $recurring->nextDueDate();

            $daysUntil = (int) now()->startOfDay()->diffInDays($dueDate);

            // Only generate if within the 60-day window
            if ($daysUntil > 60) {
                return;
            }

            // Skip if a charge already exists for this recurring charge in this year
            $exists = Charge::where('recurring_charge_id', $recurring->id)
                ->whereYear('due_date', $dueDate->year)
                ->exists();

            if ($exists) {
                return;
            }

            $description = null;
            if ($recurring->bundle_items) {
                $labels      = collect($recurring->bundle_items)->pluck('label')->join(', ');
                $description = 'Includes: ' . $labels;
            }

            Charge::create([
                'recurring_charge_id' => $recurring->id,
                'client_id'           => $recurring->client_id,
                'project_id'          => $recurring->project_id,
                'title'               => $recurring->title,
                'description'         => $description,
                'amount'              => $recurring->amount,
                'vat_rate'            => $recurring->vat_rate,
                'due_date'            => $dueDate,
                'status'              => ChargeStatus::Unpaid->value,
                'notes'               => $recurring->notes,
            ]);

            $generated++;
            $this->info("Generated charge for [{$recurring->client->name}] — {$recurring->title} due {$dueDate->format('d M Y')}");
        });

        $this->info("Done. {$generated} charge(s) generated.");
    }
}
