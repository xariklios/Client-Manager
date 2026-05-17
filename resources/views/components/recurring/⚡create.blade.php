<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Client;
use App\Models\Project;
use App\Models\RecurringCharge;
use App\Enums\RecurringChargeCategory;

new class extends Component
{
    public string|int|null $client_id  = null;
    public string|int|null $project_id = null;
    public string $title          = '';
    public string $category       = 'hosting';
    public array  $bundle_items   = [];
    public string $amount         = '';
    public string $due_day        = '1';
    public string $due_month      = '1';
    public int    $interval_years = 1;
    public string $start_year     = '';
    public int    $vat_rate       = 24;
    public bool   $notify_client  = true;
    public bool   $active         = true;
    public string $notes          = '';

    public function mount(): void
    {
        $this->start_year = (string) now()->year;
    }

    public function updatedClientId(): void
    {
        $this->project_id = null;
    }

    public function updatedCategory(): void
    {
        if ($this->category === 'bundle' && empty($this->bundle_items)) {
            $this->bundle_items = [
                ['label' => 'Hosting',     'amount' => ''],
                ['label' => 'Maintenance', 'amount' => ''],
            ];
        }
    }

    public function addBundleItem(): void
    {
        $this->bundle_items[] = ['label' => '', 'amount' => ''];
    }

    public function removeBundleItem(int $index): void
    {
        array_splice($this->bundle_items, $index, 1);
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

    #[Computed]
    public function bundleTotal(): float
    {
        return collect($this->bundle_items)->sum(fn($i) => (float) ($i['amount'] ?? 0));
    }

    public function months(): array
    {
        return [
            1 => 'January', 2 => 'February', 3 => 'March',    4 => 'April',
            5 => 'May',      6 => 'June',      7 => 'July',     8 => 'August',
            9 => 'September',10 => 'October', 11 => 'November',12 => 'December',
        ];
    }

    public function categories(): array
    {
        return RecurringChargeCategory::cases();
    }

    public function save(): void
    {
        $isBundle = $this->category === 'bundle';

        $rules = [
            'client_id'      => 'required|exists:clients,id',
            'project_id'     => 'nullable|exists:projects,id',
            'title'          => 'required|string|max:255',
            'category'       => 'required|in:hosting,domain,maintenance,bundle,other',
            'vat_rate'       => 'required|in:0,24',
            'due_day'        => 'required|integer|min:1|max:31',
            'due_month'      => 'required|integer|min:1|max:12',
            'interval_years' => 'required|in:1,2',
            'start_year'     => 'required|integer|min:2000|max:2100',
        ];

        if ($isBundle) {
            $rules['bundle_items']          = 'required|array|min:2';
            $rules['bundle_items.*.label']  = 'required|string|max:100';
            $rules['bundle_items.*.amount'] = 'required|numeric|min:0.01';
        } else {
            $rules['amount'] = 'required|numeric|min:0.01';
        }

        $this->validate($rules);

        $amount = $isBundle
            ? collect($this->bundle_items)->sum(fn($i) => (float) $i['amount'])
            : (float) $this->amount;

        RecurringCharge::create([
            'client_id'      => $this->client_id,
            'project_id'     => $this->project_id ?: null,
            'title'          => $this->title,
            'category'       => $this->category,
            'bundle_items'   => $isBundle ? $this->bundle_items : null,
            'amount'         => $amount,
            'vat_rate'       => $this->vat_rate,
            'due_day'        => $this->due_day,
            'due_month'      => $this->due_month,
            'interval_years' => $this->interval_years,
            'start_year'     => $this->start_year,
            'notify_client'  => $this->notify_client,
            'active'         => $this->active,
            'notes'          => $this->notes ?: null,
        ]);

        $this->redirect('/recurring', navigate: true);
    }
};
?>

<div>
    <div class="mb-6">
        <a href="/recurring" class="text-sm text-gray-500 hover:text-gray-700">← Back to recurring charges</a>
    </div>

    <h1 class="text-2xl font-bold text-gray-900 mb-6">Add Recurring Charge</h1>

    <form wire:submit="save" class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6 space-y-5">

            {{-- Client --}}
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

            {{-- Project --}}
            @if($this->projects->isNotEmpty())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                    <select wire:model="project_id"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">No specific project</option>
                        @foreach($this->projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Title + Category --}}
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                    <input wire:model="title" type="text" placeholder="e.g. Annual Hosting"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select wire:model.live="category"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($this->categories() as $cat)
                            <option value="{{ $cat->value }}">{{ $cat->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Amount OR Bundle items --}}
            @if($category === 'bundle')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Bundle Items <span class="text-red-500">*</span>
                        <span class="font-normal text-gray-400 ml-1">— at least 2 services</span>
                    </label>

                    <div class="space-y-2">
                        @foreach($bundle_items as $i => $item)
                            <div class="flex gap-2 items-start">
                                <div class="flex-1">
                                    <input wire:model="bundle_items.{{ $i }}.label"
                                           type="text" placeholder="e.g. Hosting"
                                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @error("bundle_items.$i.label") <p class="text-red-500 text-xs mt-0.5">{{ $message }}</p> @enderror
                                </div>
                                <div class="w-36">
                                    <div class="flex items-center border border-gray-300 rounded overflow-hidden">
                                        <span class="px-2 text-gray-400 text-sm bg-gray-50 border-r border-gray-300 py-2">€</span>
                                        <input wire:model.live="bundle_items.{{ $i }}.amount"
                                               type="number" step="0.01" min="0" placeholder="0.00"
                                               class="w-full px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    @error("bundle_items.$i.amount") <p class="text-red-500 text-xs mt-0.5">{{ $message }}</p> @enderror
                                </div>
                                @if(count($bundle_items) > 2)
                                    <button type="button" wire:click="removeBundleItem({{ $i }})"
                                            class="mt-1.5 text-gray-400 hover:text-red-500 text-lg leading-none">×</button>
                                @else
                                    <div class="w-5"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <button type="button" wire:click="addBundleItem"
                            class="mt-2 text-sm text-blue-600 hover:underline">+ Add service</button>

                    @if($this->bundleTotal > 0)
                        <div class="mt-3 flex justify-between items-center text-sm font-semibold text-gray-900 border-t border-gray-200 pt-2">
                            <span>Total</span>
                            <span>€{{ number_format($this->bundleTotal, 2) }}</span>
                        </div>
                    @endif

                    @error('bundle_items') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            @else
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (€) <span class="text-red-500">*</span></label>
                    <input wire:model="amount" type="number" step="0.01" min="0.01" placeholder="200.00"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            @endif

            {{-- Due date: day + month --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Due Date (every year) <span class="text-red-500">*</span></label>
                <div class="flex gap-3">
                    <div class="w-28">
                        <input wire:model="due_day" type="number" min="1" max="31" placeholder="Day"
                               class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('due_day') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex-1">
                        <select wire:model="due_month"
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($this->months() as $num => $name)
                                <option value="{{ $num }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('due_month') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-1">A charge will be created 60 days before this date.</p>
            </div>

            {{-- Recurrence interval + start year --}}
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Recurrence</label>
                    <div class="flex rounded-md border border-gray-300 overflow-hidden text-sm">
                        <button type="button" wire:click="$set('interval_years', 1)"
                                class="flex-1 px-3 py-2 {{ $interval_years == 1 ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                            Annual
                        </button>
                        <button type="button" wire:click="$set('interval_years', 2)"
                                class="flex-1 px-3 py-2 border-l border-gray-300 {{ $interval_years == 2 ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                            Every 2 Years
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        First Due Year <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="start_year" type="number" min="2000" max="2100" placeholder="{{ now()->year }}"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @if($interval_years == 2)
                        <p class="text-xs text-gray-400 mt-1">The year of the first renewal — defines the 2-year cycle.</p>
                    @endif
                    @error('start_year') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- VAT --}}
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
            </div>

            {{-- Notify client --}}
            <div class="flex items-start gap-3">
                <input type="checkbox" wire:model="notify_client" id="notify_client"
                       class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <div>
                    <label for="notify_client" class="text-sm font-medium text-gray-700">Send reminder emails to client</label>
                    <p class="text-xs text-gray-400">Client will receive reminders at 60, 30, 15 days before due date, and every 3 days after until paid.</p>
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea wire:model="notes" rows="2" placeholder="Internal notes (not sent to client)"
                          class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

        </div>

        <div class="flex justify-end gap-3">
            <a href="/recurring" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                Save
            </button>
        </div>
    </form>
</div>
