<?php

use Livewire\Component;
use App\Models\Client;

new class extends Component
{
    public Client $client;

    public function mount(Client $client): void
    {
        $this->client = $client;
    }
};
?>

<div>
    <div class="mb-6">
        <a href="/clients" class="text-sm text-gray-500 hover:text-gray-700">← Back to clients</a>
    </div>

    {{-- Client header --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $client->name }}</h1>
                @if($client->contact_person)
                    <p class="text-gray-500 text-sm mt-1">{{ $client->contact_person }}</p>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <span class="px-2 py-0.5 rounded text-xs font-medium {{ $client->status->color() }}">
                    {{ $client->status->label() }}
                </span>
                <a href="/clients/{{ $client->id }}/edit"
                   class="text-sm text-blue-600 hover:underline">Edit</a>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
            @if($client->email)
                <div>
                    <span class="text-gray-500">Email</span>
                    <p class="text-gray-900">{{ $client->email }}</p>
                </div>
            @endif
            @if($client->phone)
                <div>
                    <span class="text-gray-500">Phone</span>
                    <p class="text-gray-900">{{ $client->phone }}</p>
                </div>
            @endif
            @if($client->vat_number)
                <div>
                    <span class="text-gray-500">VAT</span>
                    <p class="text-gray-900">{{ $client->vat_number }}</p>
                </div>
            @endif
            @if($client->address)
                <div>
                    <span class="text-gray-500">Address</span>
                    <p class="text-gray-900 whitespace-pre-line">{{ $client->address }}</p>
                </div>
            @endif
        </div>

        @if($client->notes)
            <div class="mt-4 text-sm">
                <span class="text-gray-500">Notes</span>
                <p class="text-gray-900 mt-1 whitespace-pre-line">{{ $client->notes }}</p>
            </div>
        @endif
    </div>

    {{-- Projects --}}
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Projects</h2>
        <a href="/clients/{{ $client->id }}/projects/create"
           class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
            Add Project
        </a>
    </div>

    @if($client->projects->isEmpty())
        <p class="text-gray-500 text-sm">No projects yet. <a href="/clients/{{ $client->id }}/projects/create" class="text-blue-600 hover:underline">Add one</a>.</p>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Name</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Domain</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Renewal</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($client->projects as $project)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $project->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $project->domain ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $project->renewal_date?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium {{ $project->status->color() }}">
                                    {{ $project->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="/projects/{{ $project->id }}/edit"
                                   class="text-blue-600 hover:underline text-xs">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Charges --}}
    <div class="flex items-center justify-between mt-8 mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Charges</h2>
        <a href="/charges/create?client={{ $client->id }}"
           class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
            Add Charge
        </a>
    </div>

    @php $charges = $client->charges()->with('project')->latest()->get(); @endphp

    @if($charges->isEmpty())
        <p class="text-gray-500 text-sm">No charges yet.</p>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Title</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Project</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Due</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Amount</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($charges as $charge)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $charge->title }}</td>
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
    @endif
</div>
