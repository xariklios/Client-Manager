<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use App\Models\Charge;

new class extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $filter = 'unpaid';

    #[Url(history: true)]
    public string $search = '';

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function charges()
    {
        return Charge::with(['client', 'project'])
            ->when($this->filter === 'unpaid',  fn($q) => $q->unpaid())
            ->when($this->filter === 'overdue', fn($q) => $q->overdue())
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhereHas('client', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            }))
            ->latest()
            ->paginate(20);
    }

    #[Computed]
    public function totalUnpaid(): string
    {
        return number_format(Charge::unpaid()->sum('amount'), 2);
    }
};
?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Charges</h1>
        <a href="/charges/create"
           class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
            Add Charge
        </a>
    </div>

    {{-- Summary --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6 flex items-center gap-2 text-sm">
        <span class="text-gray-500">Total unpaid:</span>
        <span class="font-semibold text-red-600">€{{ $this->totalUnpaid }}</span>
    </div>

    <div class="flex flex-wrap items-center gap-3 mb-4">
        {{-- Filter tabs --}}
        <div class="flex gap-1">
            @foreach(['unpaid' => 'Unpaid', 'overdue' => 'Overdue', 'all' => 'All'] as $value => $label)
                <button wire:click="$set('filter', '{{ $value }}')"
                        class="px-3 py-1.5 text-sm rounded
                            {{ $filter === $value
                                ? 'bg-blue-600 text-white'
                                : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Search --}}
        <input type="text" wire:model.live.debounce.300ms="search"
               placeholder="Search by title or client…"
               class="border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent w-56" />
    </div>

    @if($this->charges->isEmpty())
        <p class="text-gray-500 text-sm">No charges found.</p>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Client</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Title</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Project</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Due</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Amount</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($this->charges as $charge)
                        <tr class="hover:bg-gray-50 {{ $charge->status->value === 'unpaid' && $charge->due_date?->isPast() ? 'bg-red-50' : '' }}">
                            <td class="px-4 py-3 font-medium text-gray-900">
                                <a href="/clients/{{ $charge->client_id }}" class="hover:underline">
                                    {{ $charge->client->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-gray-900">{{ $charge->title }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $charge->project?->name ?? '—' }}</td>
                            <td class="px-4 py-3 {{ $charge->due_date?->isPast() && $charge->status->value !== 'paid' ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                                {{ $charge->due_date?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">
                                €{{ number_format($charge->amount, 2) }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium {{ $charge->status->color() }}">
                                    {{ $charge->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="/charges/{{ $charge->id }}/edit"
                                   class="text-blue-600 hover:underline text-xs">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $this->charges->links() }}
        </div>
    @endif
</div>
