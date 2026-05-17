<?php

use Livewire\Component;
use App\Models\Charge;
use App\Models\Payment;

new class extends Component
{
    public Charge $charge;

    public string $amount = '';
    public string $payment_date = '';
    public string $payment_method = '';
    public string $notes = '';

    public function mount(Charge $charge): void
    {
        $this->charge       = $charge;
        $this->payment_date = now()->toDateString();
        $this->amount       = number_format($charge->remainingBalance(), 2, '.', '');
    }

    public function save(): void
    {
        $this->validate([
            'amount'         => 'required|numeric|min:0.01',
            'payment_date'   => 'required|date',
            'payment_method' => 'nullable|string|max:255',
        ]);

        $this->charge->payments()->create([
            'amount'         => $this->amount,
            'payment_date'   => $this->payment_date,
            'payment_method' => $this->payment_method ?: null,
            'notes'          => $this->notes ?: null,
        ]);

        $this->charge->syncStatus();

        $this->redirect('/charges/' . $this->charge->id . '/edit', navigate: true);
    }
};
?>

<div>
    <div class="mb-6">
        <a href="/charges/{{ $charge->id }}/edit" class="text-sm text-gray-500 hover:text-gray-700">
            ← Back to charge
        </a>
    </div>

    <h1 class="text-2xl font-bold text-gray-900 mb-1">Record Payment</h1>
    <p class="text-sm text-gray-500 mb-6">
        {{ $charge->title }} &middot;
        <span class="font-medium text-gray-700">€{{ number_format($charge->amount, 2) }} total</span> &middot;
        <span class="font-medium text-red-600">€{{ number_format($charge->remainingBalance(), 2) }} remaining</span>
    </p>

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

        <div class="flex justify-end gap-3 pt-2">
            <a href="/charges/{{ $charge->id }}/edit"
               class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            <button type="submit"
                    class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                Record Payment
            </button>
        </div>

    </form>
</div>
