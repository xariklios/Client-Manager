<?php

use Livewire\Component;
use App\Models\Client;
use App\Enums\ClientStatus;

new class extends Component
{
    public string $name = '';
    public string $contact_person = '';
    public string $email = '';
    public string $phone = '';
    public string $vat_number = '';
    public string $address = '';
    public string $notes = '';
    public string $status = 'active';

    public function save(): void
    {
        $this->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive,archived',
        ]);

        Client::create([
            'name'           => $this->name,
            'contact_person' => $this->contact_person ?: null,
            'email'          => $this->email ?: null,
            'phone'          => $this->phone ?: null,
            'vat_number'     => $this->vat_number ?: null,
            'address'        => $this->address ?: null,
            'notes'          => $this->notes ?: null,
            'status'         => $this->status,
        ]);

        $this->redirect('/clients', navigate: true);
    }

    public function statuses(): array
    {
        return ClientStatus::cases();
    }
};
?>

<div>
    <div class="mb-6">
        <a href="/clients" class="text-sm text-gray-500 hover:text-gray-700">← Back to clients</a>
    </div>

    <h1 class="text-2xl font-bold text-gray-900 mb-6">Add Client</h1>

    <form wire:submit="save" class="bg-white rounded-lg shadow p-6 space-y-5">

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                <input wire:model="name" type="text"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                <input wire:model="contact_person" type="text"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input wire:model="email" type="email"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input wire:model="phone" type="text"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">VAT Number</label>
                <input wire:model="vat_number" type="text"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
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

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <textarea wire:model="address" rows="2"
                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea wire:model="notes" rows="3"
                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="/clients" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                Save Client
            </button>
        </div>

    </form>
</div>
