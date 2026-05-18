<?php

use App\Models\Offer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

// Auth
Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'login')->name('login');
});

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/login');
})->middleware('auth')->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::livewire('/', 'dashboard');

    // Clients
    Route::livewire('/clients', 'clients.index');
    Route::livewire('/clients/create', 'clients.create');
    Route::livewire('/clients/{client}', 'clients.show');
    Route::livewire('/clients/{client}/edit', 'clients.edit');

    // Projects
    Route::livewire('/clients/{client}/projects/create', 'projects.create');
    Route::livewire('/projects/{project}/edit', 'projects.edit');

    // Charges
    Route::livewire('/charges', 'charges.index');
    Route::livewire('/charges/create', 'charges.create');
    Route::livewire('/charges/{charge}/edit', 'charges.edit');

    // Offers
    Route::livewire('/offers', 'offers.index');
    Route::livewire('/offers/create', 'offers.create');
    Route::livewire('/offers/{offer}/edit', 'offers.edit');
    Route::get('/offers/{offer}/pdf', function (Offer $offer) {
        $offer->load(['client', 'project', 'items']);
        $pdf = Pdf::loadView('offers.pdf', compact('offer'))->setPaper('a4', 'portrait');
        $filename = 'offer-' . str_pad($offer->id, 4, '0', STR_PAD_LEFT) . '-' . Str::slug($offer->title) . '.pdf';
        return $pdf->download($filename);
    })->name('offers.pdf');

    // Recurring charges
    Route::livewire('/recurring', 'recurring.index');
    Route::livewire('/recurring/create', 'recurring.create');
    Route::livewire('/recurring/{recurringCharge}', 'recurring.edit');

    // Broadcast
    Route::livewire('/broadcast', 'broadcast.index');

    // Email log
    Route::livewire('/email-logs', 'email-logs.index');

    // Payments
    Route::livewire('/charges/{charge}/payments/create', 'payments.create');
    Route::livewire('/payments/{payment}/edit', 'payments.edit');

      // PREVIEW ONLY — remove before production
  Route::get('/preview-email/{charge}', function (App\Models\Charge $charge) {
      return new App\Mail\UpcomingChargeReminder($charge, 30);
  })->middleware('auth');
});
