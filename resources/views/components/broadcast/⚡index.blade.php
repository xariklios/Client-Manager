<?php

use App\Mail\ClientBroadcast;
use App\Models\Client;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public array $selected  = [];
    public string $subject  = '';
    public string $body     = '';
    public bool   $sent     = false;
    public int    $sentCount = 0;

    #[Computed]
    public function clients(): \Illuminate\Database\Eloquent\Collection
    {
        return Client::whereNotNull('email')->orderBy('name')->get();
    }

    public function selectAll(): void
    {
        $this->selected = $this->clients->pluck('id')->map(fn($id) => (string) $id)->all();
    }

    public function deselectAll(): void
    {
        $this->selected = [];
    }

    public function send(): void
    {
        $this->validate([
            'subject'    => 'required|string|max:255',
            'body'       => 'required|string',
            'selected'   => 'required|array|min:1',
            'selected.*' => 'exists:clients,id',
        ]);

        $clients = Client::whereIn('id', $this->selected)->whereNotNull('email')->get();

        foreach ($clients as $client) {
            Mail::to($client->email)->send(new ClientBroadcast($this->subject, $this->body, $client));
            EmailLog::create(['type' => 'broadcast', 'recipient' => $client->email, 'subject' => $this->subject, 'records_count' => 1]);
        }

        $this->sentCount = $clients->count();
        $this->sent      = true;
        $this->subject   = '';
        $this->body      = '';
        $this->selected  = [];
    }

    public function compose(): void
    {
        $this->sent      = false;
        $this->sentCount = 0;
    }
}; ?>

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Broadcast Email</h1>
        <p class="text-sm text-gray-500 mt-1">Send a message to all or selected clients.</p>
    </div>

    @if($sent)
        <div class="bg-green-50 border border-green-200 rounded-xl p-10 text-center">
            <svg class="w-12 h-12 text-green-500 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-green-800 font-semibold text-lg">
                Sent to {{ $sentCount }} {{ $sentCount === 1 ? 'client' : 'clients' }}
            </p>
            <button wire:click="compose" class="mt-4 text-sm text-indigo-600 hover:underline">
                Compose another message
            </button>
        </div>
    @else
        <div class="grid grid-cols-3 gap-6">

            {{-- Client list --}}
            <div class="col-span-1">
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-700">Recipients</span>
                        <div class="flex gap-2 text-xs">
                            <button wire:click="selectAll" class="text-indigo-600 hover:underline">All</button>
                            <span class="text-gray-300">|</span>
                            <button wire:click="deselectAll" class="text-gray-400 hover:underline">None</button>
                        </div>
                    </div>

                    <ul class="divide-y divide-gray-50 max-h-96 overflow-y-auto">
                        @foreach($this->clients as $client)
                            <li class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50">
                                <input type="checkbox"
                                       wire:model="selected"
                                       value="{{ $client->id }}"
                                       id="client-{{ $client->id }}"
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="client-{{ $client->id }}" class="flex-1 min-w-0 cursor-pointer">
                                    <div class="text-sm font-medium text-gray-800 truncate">{{ $client->name }}</div>
                                    <div class="text-xs text-gray-400 truncate">{{ $client->email }}</div>
                                </label>
                            </li>
                        @endforeach
                    </ul>

                    @if(count($selected) > 0)
                        <div class="px-4 py-2 bg-indigo-50 border-t border-indigo-100 text-xs text-indigo-700 font-medium">
                            {{ count($selected) }} {{ count($selected) === 1 ? 'recipient' : 'recipients' }} selected
                        </div>
                    @endif
                </div>
                @error('selected')
                    <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            {{-- Compose --}}
            <div class="col-span-2 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Subject</label>
                    <input wire:model="subject" type="text"
                           placeholder="e.g. Important update about your services"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('subject')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Message</label>
                    <textarea wire:model="body" rows="14"
                              placeholder="Write your message here...&#10;&#10;Supports Markdown formatting."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                    @error('body')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror

                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-400">
                        <span>Formatting:</span>
                        <code class="bg-gray-100 px-1 rounded text-gray-500">**bold**</code>
                        <code class="bg-gray-100 px-1 rounded text-gray-500">*italic*</code>
                        <code class="bg-gray-100 px-1 rounded text-gray-500">[text](url)</code>
                        <code class="bg-gray-100 px-1 rounded text-gray-500"># Heading</code>
                        <code class="bg-gray-100 px-1 rounded text-gray-500">- list item</code>
                    </div>
                </div>

                <div class="flex justify-end pt-1">
                    <button wire:click="send" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-medium px-6 py-2.5 rounded-lg transition-colors">
                        <svg wire:loading.remove wire:target="send" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                        </svg>
                        <svg wire:loading wire:target="send" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="send">Send Email</span>
                        <span wire:loading wire:target="send">Sending…</span>
                    </button>
                </div>
            </div>

        </div>
    @endif
</div>
