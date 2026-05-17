<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Offer;
use App\Models\Project;
use App\Enums\OfferStatus;

new class extends Component
{
    public Offer $offer;

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

    public function mount(Offer $offer): void
    {
        $this->offer          = $offer;
        $this->project_id     = $offer->project_id;
        $this->title          = $offer->title;
        $this->description    = $offer->description ?? '';
        $this->original_price = $offer->original_price ? (string) $offer->original_price : '';
        $this->price          = (string) $offer->total;
        $this->vat_rate       = $offer->vat_rate;
        $this->status         = $offer->status->value;
        $this->sent_date      = $offer->sent_date?->format('Y-m-d') ?? '';
        $this->accepted_date  = $offer->accepted_date?->format('Y-m-d') ?? '';
        $this->notes          = $offer->notes ?? '';
    }

    #[Computed]
    public function projects()
    {
        return Project::where('client_id', $this->offer->client_id)->orderBy('name')->get();
    }

    public function statuses(): array
    {
        return OfferStatus::cases();
    }

    public function save(): void
    {
        $this->validate([
            'project_id'     => 'nullable|exists:projects,id',
            'title'          => 'required|string|max:255',
            'price'          => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'vat_rate'       => 'required|in:0,24',
            'status'         => 'required|in:draft,sent,accepted,rejected',
            'sent_date'      => 'nullable|date',
            'accepted_date'  => 'nullable|date',
        ]);

        $this->offer->update([
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
        ]);

        $this->redirect('/offers', navigate: true);
    }

    public function delete(): void
    {
        $this->offer->delete();
        $this->redirect('/offers', navigate: true);
    }
};
?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <a href="/offers" class="text-sm text-gray-500 hover:text-gray-700">← Back to offers</a>
        <a href="/offers/{{ $offer->id }}/pdf" target="_blank"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 border border-gray-300 rounded hover:bg-gray-50">
            ↓ Download PDF
        </a>
    </div>

    <h1 class="text-2xl font-bold text-gray-900 mb-1">Edit Offer</h1>
    <p class="text-sm text-gray-500 mb-6">
        Client:
        <a href="/clients/{{ $offer->client_id }}" class="text-blue-600 hover:underline">
            {{ $offer->client->name }}
        </a>
    </p>

    <form wire:submit="save" class="space-y-6">

        {{-- Client / Project / Status --}}
        <div class="bg-white rounded-lg shadow p-6 space-y-5">

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Offer Title <span class="text-red-500">*</span></label>
                    <input wire:model="title" type="text"
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

            <div class="grid grid-cols-2 gap-5">
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

                <div class="grid grid-cols-2 gap-3">
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
        </div>

        {{-- Offer content --}}
        <div class="bg-white rounded-lg shadow p-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Offer Content</label>
            <p class="text-xs text-gray-400 mb-2">Write the full scope of work. This text will appear as-is in the PDF.</p>
            <textarea wire:model="description" rows="16"
                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono leading-relaxed"></textarea>
        </div>

        {{-- Price --}}
        <div class="bg-white rounded-lg shadow p-6 space-y-5">
            <h2 class="text-base font-semibold text-gray-900">Pricing</h2>

            <div class="grid grid-cols-3 gap-5">
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

        {{-- Internal notes --}}
        <div class="bg-white rounded-lg shadow p-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Internal Notes</label>
            <p class="text-xs text-gray-400 mb-2">Not visible to the client.</p>
            <textarea wire:model="notes" rows="3"
                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div class="flex items-center justify-between">
            <button type="button" wire:click="delete"
                    wire:confirm="Are you sure you want to delete this offer?"
                    class="px-4 py-2 text-sm text-red-600 hover:text-red-800">
                Delete offer
            </button>
            <div class="flex gap-3">
                <a href="/offers" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                    Save Changes
                </button>
            </div>
        </div>

    </form>
</div>
