<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function login(): void
    {
        $this->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('email', 'Invalid credentials.');
            return;
        }

        session()->regenerate();
        $this->redirect('/', navigate: true);
    }
};
?>

<div class="w-full max-w-sm">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Welcome back</h1>
        <p class="text-sm text-gray-500 mt-1">Sign in to your account</p>
    </div>

    <form wire:submit="login" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
            <input type="email" wire:model="email" autocomplete="email"
                   class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" />
            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
            <input type="password" wire:model="password" autocomplete="current-password"
                   class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm text-gray-900
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition" />
            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-2 pt-1">
            <input type="checkbox" wire:model="remember" id="remember"
                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
            <label for="remember" class="text-sm text-gray-600">Remember me</label>
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 text-white text-sm font-medium py-2.5 px-4 rounded-lg
                       hover:bg-indigo-700 transition-colors mt-2">
            Sign in
        </button>
    </form>

</div>
