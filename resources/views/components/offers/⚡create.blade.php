<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Client;
use App\Models\Project;
use App\Models\Offer;
use App\Enums\OfferStatus;

new class extends Component
{
    public string|int|null $client_id     = null;
    public string|int|null $project_id    = null;
    public string $title          = '';
    public string $description    = '';
    public string $original_price = '';
    public string $price          = '';
    public int    $vat_rate       = 24;
    public string $status         = 'draft';
    public string $sent_date      = '';
    public string $accepted_date  = '';
    public string $notes          = '';
    public string $internal_notes = '';

    public function updatedClientId(): void
    {
        $this->project_id = null;
    }

    #[Computed]
    public function clients()
    {
        return Client::orderBy('name')->get();
    }

    #[Computed]
    public function projects()
    {
        if (! $this->client_id) return collect();
        return Project::where('client_id', $this->client_id)->orderBy('name')->get();
    }

    public function statuses(): array
    {
        return OfferStatus::cases();
    }

    public function save(): void
    {
        $this->validate([
            'client_id'      => 'required|exists:clients,id',
            'project_id'     => 'nullable|exists:projects,id',
            'title'          => 'required|string|max:255',
            'price'          => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'vat_rate'       => 'required|in:0,24',
            'status'         => 'required|in:draft,sent,accepted,rejected',
            'sent_date'      => 'nullable|date',
            'accepted_date'  => 'nullable|date',
        ]);

        Offer::create([
            'client_id'      => $this->client_id,
            'project_id'     => $this->project_id ?: null,
            'title'          => $this->title,
            'description'    => $this->description ?: null,
            'original_price' => $this->original_price ?: null,
            'total'          => $this->price,
            'vat_rate'       => $this->vat_rate,
            'status'         => $this->status,
            'sent_date'      => $this->sent_date ?: null,
            'accepted_date'  => $this->accepted_date ?: null,
            'notes'          => $this->notes ?: null,
            'internal_notes' => $this->internal_notes ?: null,
        ]);

        $this->redirect('/offers', navigate: true);
    }
};
?>

<div>
    <div class="mb-6">
        <a href="/offers" class="text-sm text-gray-500 hover:text-gray-700">← Back to offers</a>
    </div>

    <h1 class="text-2xl font-bold text-gray-900 mb-6">New Offer</h1>

    <form wire:submit="save" class="space-y-6">

        {{-- Client / Project / Status --}}
        <div class="bg-white rounded-lg shadow p-6 space-y-5">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client <span class="text-red-500">*</span></label>
                    <select wire:model.live="client_id"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select a client…</option>
                        @foreach($this->clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                    @error('client_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                    <select wire:model="project_id"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                   {{ ! $client_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                            {{ ! $client_id ? 'disabled' : '' }}>
                        <option value="">No project</option>
                        @foreach($this->projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Offer Title <span class="text-red-500">*</span></label>
                    <input wire:model="title" type="text" placeholder="e.g. Website Redesign Proposal"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sent Date</label>
                    <input wire:model="sent_date" type="date"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Accepted Date</label>
                    <input wire:model="accepted_date" type="date"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        {{-- Offer content --}}
        <div class="bg-white rounded-lg shadow p-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Περιεχόμενο προσφοράς</label>
            <p class="text-xs text-gray-400 mb-2">Εμφανίζεται στο PDF.</p>
            <textarea wire:model="description" rows="14" placeholder="Describe the work included in this offer…"
                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono leading-relaxed"></textarea>
            <p class="mt-1.5 text-xs text-gray-400"><code class="bg-gray-100 px-1 rounded font-mono">**bold**</code> → <strong>έντονο</strong> &nbsp;·&nbsp; Enter = νέα γραμμή στο PDF</p>
        </div>

        {{-- Price --}}
        <div class="bg-white rounded-lg shadow p-6 space-y-5">
            <h2 class="text-base font-semibold text-gray-900">Pricing</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Original price (€)
                        <span class="font-normal text-gray-400">— optional, crossed out</span>
                    </label>
                    <input wire:model.live="original_price" type="number" step="0.01" min="0" placeholder="700.00"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('original_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Offer price (€ net) <span class="text-red-500">*</span></label>
                    <input wire:model.live="price" type="number" step="0.01" min="0" placeholder="500.00"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">VAT</label>
                    <div class="flex rounded-md border border-gray-300 overflow-hidden text-sm">
                        <button type="button" wire:click="$set('vat_rate', 0)"
                                class="flex-1 px-3 py-2 {{ $vat_rate == 0 ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                            0%
                        </button>
                        <button type="button" wire:click="$set('vat_rate', 24)"
                                class="flex-1 px-3 py-2 border-l border-gray-300 {{ $vat_rate == 24 ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                            24%
                        </button>
                    </div>
                </div>
            </div>

            {{-- Live price preview --}}
            @if($price)
                <div class="bg-gray-50 rounded-lg p-4 text-sm space-y-1.5">
                    @if($original_price)
                        <div class="flex justify-between text-gray-400">
                            <span>Original price</span>
                            <span class="line-through">€{{ number_format((float)$original_price, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-gray-700">
                        <span>Offer price (net)</span>
                        <span>€{{ number_format((float)$price, 2) }}</span>
                    </div>
                    @if($vat_rate > 0)
                        <div class="flex justify-between text-gray-500">
                            <span>VAT {{ $vat_rate }}%</span>
                            <span>€{{ number_format((float)$price * $vat_rate / 100, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between font-semibold text-gray-900 border-t border-gray-200 pt-1.5">
                        <span>Total{{ $vat_rate == 0 ? ' (VAT exempt)' : '' }}</span>
                        <span>€{{ number_format((float)$price * (1 + $vat_rate / 100), 2) }}</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Client notes (visible in PDF) --}}
        <div class="bg-white rounded-lg shadow p-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Σημειώσεις για τον πελάτη</label>
            <p class="text-xs text-gray-400 mb-2">Εμφανίζονται στο PDF.</p>
            <textarea wire:model="notes" rows="3"
                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            <p class="mt-1.5 text-xs text-gray-400"><code class="bg-gray-100 px-1 rounded font-mono">**bold**</code> → <strong>έντονο</strong> &nbsp;·&nbsp; Enter = νέα γραμμή</p>
        </div>

        {{-- Internal notes (NOT in PDF) --}}
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-amber-400">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Internal Notes
                <span class="ml-2 text-xs font-normal text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded">Δεν εμφανίζονται στο PDF</span>
            </label>
            <textarea wire:model="internal_notes" rows="3"
                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div class="flex justify-end gap-3">
            <a href="/offers" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                Save Offer
            </button>
        </div>

    </form>
</div>
