<?php

use Livewire\Component;
use App\Models\Payment;

new class extends Component
{
    public Payment $payment;

    public string $amount = '';
    public string $payment_date = '';
    public string $payment_method = '';
    public string $notes = '';

    public function mount(Payment $payment): void
    {
        $this->payment        = $payment;
        $this->amount         = $payment->amount;
        $this->payment_date   = $payment->payment_date->format('Y-m-d');
        $this->payment_method = $payment->payment_method ?? '';
        $this->notes          = $payment->notes ?? '';
    }

    public function save(): void
    {
        $this->validate([
            'amount'         => 'required|numeric|min:0.01',
            'payment_date'   => 'required|date',
            'payment_method' => 'nullable|string|max:255',
        ]);

        $this->payment->update([
            'amount'         => $this->amount,
            'payment_date'   => $this->payment_date,
            'payment_method' => $this->payment_method ?: null,
            'notes'          => $this->notes ?: null,
        ]);

        $this->payment->charge->syncStatus();

        $this->redirect('/charges/' . $this->payment->charge_id . '/edit', navigate: true);
    }

    public function delete(): void
    {
        $chargeId = $this->payment->charge_id;
        $charge   = $this->payment->charge;

        $this->payment->delete();

        $charge->syncStatus();

        $this->redirect('/charges/' . $chargeId . '/edit', navigate: true);
    }
};
?>

<div>
    <div class="mb-6">
        <a href="/charges/{{ $payment->charge_id }}/edit" class="text-sm text-gray-500 hover:text-gray-700">
            ← Back to charge
        </a>
    </div>

    <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Payment</h1>

    <form wire:submit="save" class="bg-white rounded-lg shadow p-6 space-y-5">

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Amount (€) <span class="text-red-500">*</span></label>
                <input wire:model="amount" type="number" step="0.01" min="0.01"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date <span class="text-red-500">*</span></label>
                <input wire:model="payment_date" type="date"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('payment_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
            <input wire:model="payment_method" type="text"
                   placeholder="e.g. Bank transfer, Cash, Card, PayPal"
                   class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea wire:model="notes" rows="2"
                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div class="flex items-center justify-between pt-2">
            <button type="button" wire:click="delete"
                    wire:confirm="Delete this payment? The charge status will be recalculated."
                    class="px-4 py-2 text-sm text-red-600 hover:text-red-800">
                Delete payment
            </button>
            <div class="flex gap-3">
                <a href="/charges/{{ $payment->charge_id }}/edit"
                   class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                    Save Changes
                </button>
            </div>
        </div>

    </form>
</div>
