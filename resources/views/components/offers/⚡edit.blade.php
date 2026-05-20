<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Offer;
use App\Models\Charge;
use App\Models\Project;
use App\Enums\OfferStatus;
use App\Enums\ChargeStatus;
use App\Mail\OfferSent;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

new class extends Component
{
    public Offer $offer;

    public string|int|null $project_id     = null;
    public string          $title          = '';
    public string          $description    = '';
    public string          $original_price = '';
    public string          $price          = '';
    public int             $vat_rate       = 24;
    public string          $status         = 'draft';
    public string          $sent_date      = '';
    public string          $accepted_date  = '';
    public string          $notes          = '';
    public string          $internal_notes = '';

    public bool   $sent    = false;
    public string $sentMsg = '';

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
        $this->internal_notes = $offer->internal_notes ?? '';
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
            'internal_notes' => $this->internal_notes ?: null,
        ]);

        $this->redirect('/offers', navigate: true);
    }

    public function sendToClient(): void
    {
        if (! $this->offer->client->email) {
            $this->sentMsg = 'error:Ο πελάτης δεν έχει email.';
            return;
        }

        $this->offer->load(['client', 'project', 'items']);

        Mail::to($this->offer->client->email)->send(new OfferSent($this->offer));

        $today = now()->toDateString();
        $this->offer->update([
            'status'    => 'sent',
            'sent_date' => $today,
        ]);

        $this->status    = 'sent';
        $this->sent_date = $today;
        $this->sent      = true;
        $this->sentMsg   = 'ok:Εστάλη στο ' . $this->offer->client->email;
    }

    public function convertToCharge(): void
    {
        $charge = Charge::create([
            'client_id'   => $this->offer->client_id,
            'project_id'  => $this->offer->project_id,
            'title'       => $this->offer->title,
            'description' => 'Από προσφορά #' . str_pad($this->offer->id, 4, '0', STR_PAD_LEFT),
            'amount'      => $this->offer->total,
            'vat_rate'    => $this->offer->vat_rate,
            'status'      => ChargeStatus::Unpaid->value,
        ]);

        $this->redirect('/charges/' . $charge->id . '/edit', navigate: true);
    }

    public function delete(): void
    {
        $this->offer->delete();
        $this->redirect('/offers', navigate: true);
    }
};
?>

<div>
    {{-- Top bar --}}
    <div class="mb-6 flex items-center justify-between gap-3 flex-wrap">
        <a href="/offers" class="text-sm text-gray-500 hover:text-gray-700">← Back to offers</a>

        <div class="flex items-center gap-2 flex-wrap">

            {{-- Sent flag --}}
            @if($offer->sent_date)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                    </svg>
                    Εστάλη {{ $offer->sent_date->format('d M Y') }}
                </span>
            @endif

            {{-- Flash message --}}
            @if($sentMsg)
                @php [$type, $text] = explode(':', $sentMsg, 2); @endphp
                <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $type === 'ok' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $text }}
                </span>
            @endif

            <a href="/offers/{{ $offer->id }}/pdf" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                PDF
            </a>

            <button wire:click="sendToClient"
                    wire:confirm="Θα σταλεί email με το PDF της προσφοράς στον πελάτη {{ $offer->client->name }} ({{ $offer->client->email }}). Συνέχεια;"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-indigo-700 border border-indigo-300 bg-indigo-50 rounded-lg hover:bg-indigo-100 disabled:opacity-50">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                </svg>
                <span wire:loading.remove wire:target="sendToClient">Αποστολή στον πελάτη</span>
                <span wire:loading wire:target="sendToClient">Αποστολή…</span>
            </button>

            <button wire:click="convertToCharge"
                    wire:confirm="Θα δημιουργηθεί νέο charge από αυτή την προσφορά. Συνέχεια;"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-emerald-700 border border-emerald-300 bg-emerald-50 rounded-lg hover:bg-emerald-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                </svg>
                Μετατροπή σε Charge
            </button>
        </div>
    </div>

    <h1 class="text-2xl font-bold text-gray-900 mb-1">Edit Offer</h1>
    <p class="text-sm text-gray-500 mb-6">
        Client:
        <a href="/clients/{{ $offer->client_id }}" class="text-blue-600 hover:underline">
            {{ $offer->client->name }}
        </a>
    </p>

    <form wire:submit="save" class="space-y-6">

        {{-- Meta --}}
        <div class="bg-white rounded-lg shadow p-6 space-y-5">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Τίτλος προσφοράς <span class="text-red-500">*</span></label>
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
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
            <label class="block text-sm font-medium text-gray-700 mb-1">Περιεχόμενο προσφοράς</label>
            <p class="text-xs text-gray-400 mb-2">Εμφανίζεται στο PDF.</p>
            <textarea wire:model="description" rows="16"
                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono leading-relaxed"></textarea>
            <p class="mt-1.5 text-xs text-gray-400"><code class="bg-gray-100 px-1 rounded font-mono">**bold**</code> → <strong>έντονο</strong> &nbsp;·&nbsp; Enter = νέα γραμμή στο PDF</p>
        </div>

        {{-- Price --}}
        <div class="bg-white rounded-lg shadow p-6 space-y-5">
            <h2 class="text-base font-semibold text-gray-900">Τιμολόγηση</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Αρχική τιμή (€)
                        <span class="font-normal text-gray-400">— προαιρετικό, διαγραμμένο</span>
                    </label>
                    <input wire:model.live="original_price" type="number" step="0.01" min="0" placeholder="700.00"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('original_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Τιμή (€ χωρίς ΦΠΑ) <span class="text-red-500">*</span></label>
                    <input wire:model.live="price" type="number" step="0.01" min="0" placeholder="500.00"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ΦΠΑ</label>
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
                            <span>Αρχική τιμή</span>
                            <span class="line-through">€{{ number_format((float)$original_price, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-gray-700">
                        <span>Τιμή (χωρίς ΦΠΑ)</span>
                        <span>€{{ number_format((float)$price, 2) }}</span>
                    </div>
                    @if($vat_rate > 0)
                        <div class="flex justify-between text-gray-500">
                            <span>ΦΠΑ {{ $vat_rate }}%</span>
                            <span>€{{ number_format((float)$price * $vat_rate / 100, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between font-semibold text-gray-900 border-t border-gray-200 pt-1.5">
                        <span>Σύνολο{{ $vat_rate == 0 ? ' (απαλλαγή ΦΠΑ)' : '' }}</span>
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

        <div class="flex items-center justify-between">
            <button type="button" wire:click="delete"
                    wire:confirm="Σίγουρα θέλεις να διαγράψεις αυτή την προσφορά;"
                    class="px-4 py-2 text-sm text-red-600 hover:text-red-800">
                Διαγραφή
            </button>
            <div class="flex gap-3">
                <a href="/offers" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Ακύρωση</a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                    Αποθήκευση
                </button>
            </div>
        </div>

    </form>
</div>
