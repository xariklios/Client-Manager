<?php

use Livewire\Component;
use App\Models\Client;
use App\Models\Project;
use App\Enums\ProjectStatus;

new class extends Component
{
    public Client $client;

    public string $name = '';
    public string $domain = '';
    public string $url = '';
    public string $hosting_plan = '';
    public string $maintenance_plan = '';
    public string $tech_notes = '';
    public string $start_date = '';
    public string $renewal_date       = '';
    public string $domain_expiry_date = '';
    public string $status             = 'active';
    public string $notes = '';

    public function mount(Client $client): void
    {
        $this->client = $client;
    }

    public function save(): void
    {
        $this->validate([
            'name'         => 'required|string|max:255',
            'domain'       => 'nullable|string|max:255',
            'url'          => 'nullable|url|max:255',
            'start_date'   => 'nullable|date',
            'renewal_date'       => 'nullable|date',
            'domain_expiry_date' => 'nullable|date',
            'status'             => 'required|in:active,inactive,archived',
        ]);

        $this->client->projects()->create([
            'name'             => $this->name,
            'domain'           => $this->domain ?: null,
            'url'              => $this->url ?: null,
            'hosting_plan'     => $this->hosting_plan ?: null,
            'maintenance_plan' => $this->maintenance_plan ?: null,
            'tech_notes'       => $this->tech_notes ?: null,
            'start_date'       => $this->start_date ?: null,
            'renewal_date'       => $this->renewal_date ?: null,
            'domain_expiry_date' => $this->domain_expiry_date ?: null,
            'status'             => $this->status,
            'notes'            => $this->notes ?: null,
        ]);

        $this->redirect('/clients/' . $this->client->id, navigate: true);
    }

    public function statuses(): array
    {
        return ProjectStatus::cases();
    }
};
?>

<div>
    <div class="mb-6">
        <a href="/clients/{{ $client->id }}" class="text-sm text-gray-500 hover:text-gray-700">
            ← Back to {{ $client->name }}
        </a>
    </div>

    <h1 class="text-2xl font-bold text-gray-900 mb-6">Add Project</h1>

    <form wire:submit="save" class="bg-white rounded-lg shadow p-6 space-y-5">

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Project Name <span class="text-red-500">*</span></label>
                <input wire:model="name" type="text"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Domain</label>
                <input wire:model="domain" type="text" placeholder="example.com"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Website URL</label>
                <input wire:model="url" type="text" placeholder="https://www.example.com"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('url') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hosting Plan</label>
                <input wire:model="hosting_plan" type="text"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Maintenance Plan</label>
                <input wire:model="maintenance_plan" type="text"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input wire:model="start_date" type="date"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hosting Renewal Date</label>
                <input wire:model="renewal_date" type="date"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('renewal_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Domain Expiry Date</label>
                <input wire:model="domain_expiry_date" type="date"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('domain_expiry_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div></div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Technology Notes</label>
            <textarea wire:model="tech_notes" rows="2"
                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Internal Notes</label>
            <textarea wire:model="notes" rows="3"
                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="/clients/{{ $client->id }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                Save Project
            </button>
        </div>

    </form>
</div>
