<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use App\Models\Client;

new class extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function clients()
    {
        return Client::query()
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('contact_person', 'like', '%' . $this->search . '%');
            }))
            ->latest()
            ->paginate(20);
    }
};
?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Clients</h1>
        <a href="/clients/create"
           class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
            Add Client
        </a>
    </div>

    <div class="mb-4">
        <input type="text" wire:model.live.debounce.300ms="search"
               placeholder="Search by name, email, or contact…"
               class="w-full max-w-sm border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent" />
    </div>

    @if($this->clients->isEmpty())
        <p class="text-gray-500">
            {{ $search ? 'No clients match your search.' : 'No clients yet. Add your first one.' }}
        </p>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Name</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Contact</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Email</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Phone</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($this->clients as $client)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $client->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $client->contact_person ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $client->email ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $client->phone ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium {{ $client->status->color() }}">
                                    {{ $client->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-3">
                                <a href="/clients/{{ $client->id }}"
                                   class="text-gray-600 hover:underline text-xs">View</a>
                                <a href="/clients/{{ $client->id }}/edit"
                                   class="text-blue-600 hover:underline text-xs">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $this->clients->links() }}
        </div>
    @endif
</div>
