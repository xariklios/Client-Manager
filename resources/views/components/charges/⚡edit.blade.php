<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Charge;
use App\Models\Project;
use App\Enums\ChargeStatus;

new class extends Component
{
    public Charge $charge;

    public string|int|null $project_id = null;
    public string $title = '';
    public string $description = '';
    public string $amount = '';
    public string $due_date = '';
    public string $status = 'unpaid';
    public string $notes    = '';
    public int    $vat_rate = 24;

    public function mount(Charge $charge): void
    {
        $this->charge      = $charge;
        $this->project_id  = $charge->project_id;
        $this->title       = $charge->title;
        $this->description = $charge->description ?? '';
        $this->amount      = $charge->amount;
        $this->due_date    = $charge->due_date?->format('Y-m-d') ?? '';
        $this->status      = $charge->status->value;
        $this->notes       = $charge->notes ?? '';
        $this->vat_rate    = $charge->vat_rate;
    }

    #[Computed]
    public function projects()
    {
        return Project::where('client_id', $this->charge->client_id)->orderBy('name')->get();
    }

    public function statuses(): array
    {
        return ChargeStatus::cases();
    }

    public function save(): void
    {
        $this->validate([
            'project_id' => 'nullable|exists:projects,id',
            'title'      => 'required|string|max:255',
            'amount'     => 'required|numeric|min:0',
            'vat_rate'   => 'required|in:0,24',
            'due_date'   => 'nullable|date',
            'status'     => 'required|in:unpaid,partially_paid,paid,cancelled',
        ]);

        $this->charge->update([
            'project_id'  => $this->project_id ?: null,
            'title'       => $this->title,
            'description' => $this->description ?: null,
            'amount'      => $this->amount,
            'vat_rate'    => $this->vat_rate,
            'due_date'    => $this->due_date ?: null,
            'status'      => $this->status,
            'notes'       => $this->notes ?: null,
        ]);

        $this->redirect('/charges', navigate: true);
    }

    public function delete(): void
    {
        $this->charge->delete();
        $this->redirect('/charges', navigate: true);
    }
};
?>

<div>
    <div class="mb-6">
        <a href="/charges" class="text-sm text-gray-500 hover:text-gray-700">← Back to charges</a>
    </div>

    <h1 class="text-2xl font-bold text-gray-900 mb-1">Edit Charge</h1>
    <p class="text-sm text-gray-500 mb-6">
        Client:
        <a href="/clients/{{ $charge->client_id }}" class="text-blue-600 hover:underline">
            {{ $charge->client->name }}
        </a>
    </p>

    <form wire:submit="save" class="bg-white rounded-lg shadow p-6 space-y-5">

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input wire:model="title" type="text"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                <select wire:model="project_id"
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">No project</option>
                    @foreach($this->projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Amount net (€) <span class="text-red-500">*</span></label>
                <input wire:model.live="amount" type="number" step="0.01" min="0"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                <input wire:model="due_date" type="date"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">VAT</label>
            <div class="flex rounded-md border border-gray-300 overflow-hidden text-sm w-fit">
                <button type="button" wire:click="$set('vat_rate', 0)"
                        class="px-4 py-2 {{ $vat_rate == 0 ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                    0% (exempt)
                </button>
                <button type="button" wire:click="$set('vat_rate', 24)"
                        class="px-4 py-2 border-l border-gray-300 {{ $vat_rate == 24 ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                    24%
                </button>
            </div>
            @if($amount && $vat_rate > 0)
                <p class="text-xs text-gray-400 mt-1">
                    Net €{{ number_format((float)$amount, 2) }} + VAT €{{ number_format((float)$amount * $vat_rate / 100, 2) }} = Gross €{{ number_format((float)$amount * (1 + $vat_rate / 100), 2) }}
                </p>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select wire:model="status"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @foreach($this->statuses() as $s)
                    <option value="{{ $s->value }}">{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea wire:model="description" rows="2"
                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea wire:model="notes" rows="2"
                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div class="flex items-center justify-between pt-2">
            <button type="button" wire:click="delete"
                    wire:confirm="Are you sure you want to delete this charge?"
                    class="px-4 py-2 text-sm text-red-600 hover:text-red-800">
                Delete charge
            </button>
            <div class="flex gap-3">
                <a href="/charges" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                    Save Changes
                </button>
            </div>
        </div>

    </form>

    {{-- Payments panel --}}
    <div class="mt-8">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Payments</h2>
                @php
                    $totalPaid      = $charge->totalPaid();
                    $remaining      = $charge->remainingBalance();
                @endphp
                <p class="text-sm text-gray-500 mt-0.5">
                    Paid: <span class="font-medium text-gray-900">€{{ number_format($totalPaid, 2) }}</span>
                    &middot;
                    Remaining: <span class="font-medium {{ $remaining > 0 ? 'text-red-600' : 'text-green-600' }}">€{{ number_format($remaining, 2) }}</span>
                </p>
            </div>
            @if($charge->status->value !== 'paid' && $charge->status->value !== 'cancelled')
                <a href="/charges/{{ $charge->id }}/payments/create"
                   class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                    Record Payment
                </a>
            @endif
        </div>

        @php $payments = $charge->payments()->latest('payment_date')->get(); @endphp

        @if($payments->isEmpty())
            <p class="text-sm text-gray-500">No payments recorded yet.</p>
        @else
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Method</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Notes</th>
                            <th class="text-right px-4 py-3 font-medium text-gray-600">Amount</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($payments as $payment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-900">{{ $payment->payment_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $payment->payment_method ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $payment->notes ?? '—' }}</td>
                                <td class="px-4 py-3 text-right font-medium text-green-700">
                                    €{{ number_format($payment->amount, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="/payments/{{ $payment->id }}/edit"
                                       class="text-blue-600 hover:underline text-xs">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
