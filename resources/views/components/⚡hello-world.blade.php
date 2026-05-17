<?php

use Livewire\Component;

new class extends Component
{
    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }
};
?>

<div>
    <h1 class="text-2xl font-bold mb-4">Hello from Livewire v4</h1>
    <p class="mb-4 text-gray-600">Count: {{ $count }}</p>
    <button wire:click="increment"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        Click me
    </button>
</div>
