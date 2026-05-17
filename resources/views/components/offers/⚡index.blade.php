<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use App\Models\Offer;

new class extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $filter = 'all';

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
    public function offers()
    {
        return Offer::with(['client', 'project'])
            ->when($this->filter !== 'all', fn($q) => $q->where('status', $this->filter))
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhereHas('client', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            }))
            ->latest()
            ->paginate(20);
    }
};
?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Offers</h1>
        <a href="/offers/create"
           class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
            New Offer
        </a>
    </div>

    <div class="flex flex-wrap items-center gap-3 mb-4">
        {{-- Filter tabs --}}
        <div class="flex gap-1">
            @foreach(['all' => 'All', 'draft' => 'Draft', 'sent' => 'Sent', 'accepted' => 'Accepted', 'rejected' => 'Rejected'] as $value => $label)
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

    @if($this->offers->isEmpty())
        <p class="text-gray-500 text-sm">No offers found.</p>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Client</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Title</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Project</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Sent</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Total</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($this->offers as $offer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">
                                <a href="/clients/{{ $offer->client_id }}" class="hover:underline">
                                    {{ $offer->client->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-gray-900">{{ $offer->title }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $offer->project?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500">
                                {{ $offer->sent_date?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">
                                €{{ number_format($offer->total, 2) }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium {{ $offer->status->color() }}">
                                    {{ $offer->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="/offers/{{ $offer->id }}/edit"
                                   class="text-blue-600 hover:underline text-xs">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $this->offers->links() }}
        </div>
    @endif
</div>
