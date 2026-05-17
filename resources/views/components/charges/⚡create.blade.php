<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Charge;
use App\Models\Client;
use App\Models\Project;
use App\Enums\ChargeStatus;

new class extends Component
{
    public string|int|null $client_id = null;
    public string|int|null $project_id = null;
    public string $title = '';
    public string $description = '';
    public string $amount = '';
    public string $due_date = '';
    public string $status = 'unpaid';
    public string $notes    = '';
    public int    $vat_rate = 24;

    // When the client changes, clear the project selection
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
        if (!$this->client_id) {
            return collect();
        }

        return Project::where('client_id', $this->client_id)->orderBy('name')->get();
    }

    public function statuses(): array
    {
        return ChargeStatus::cases();
    }

    public function save(): void
    {
        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'title'     => 'required|string|max:255',
            'amount'    => 'required|numeric|min:0',
            'vat_rate'  => 'required|in:0,24',
            'due_date'  => 'nullable|date',
            'status'    => 'required|in:unpaid,partially_paid,paid,cancelled',
        ]);

        Charge::create([
            'client_id'   => $this->client_id,
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
};
?>

<div>
    <div class="mb-6">
        <a href="/charges" class="text-sm text-gray-500 hover:text-gray-700">← Back to charges</a>
    </div>

    <h1 class="text-2xl font-bold text-gray-900 mb-6">Add Charge</h1>

    <form wire:submit="save" class="bg-white rounded-lg shadow p-6 space-y-5">

        <div class="grid grid-cols-2 gap-5">
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
                               {{ !$client_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ !$client_id ? 'disabled' : '' }}>
                    <option value="">No project</option>
                    @foreach($this->projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input wire:model="title" type="text" placeholder="e.g. Hosting renewal, WPML license"
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Amount net (€) <span class="text-red-500">*</span></label>
                <input wire:model.live="amount" type="number" step="0.01" min="0" placeholder="0.00"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                <input wire:model="due_date" type="date"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('due_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea wire:model="description" rows="2"
                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea wire:model="notes" rows="2"
                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="/charges" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                Save Charge
            </button>
        </div>

    </form>
</div>
