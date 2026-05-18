<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Charge;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;

new class extends Component
{
    #[Computed]
    public function totalUnpaid(): string
    {
        return number_format(Charge::unpaid()->sum('amount'), 2);
    }

    #[Computed]
    public function overdueCharges()
    {
        return Charge::overdue()->with('client')->latest('due_date')->get();
    }

    #[Computed]
    public function recentClients()
    {
        return Client::latest()->limit(5)->get();
    }

    #[Computed]
    public function collectedThisYear(): string
    {
        return number_format(
            Payment::whereYear('payment_date', now()->year)->sum('amount'),
            2
        );
    }

    #[Computed]
    public function totalInvoicedThisYear(): string
    {
        return number_format(
            Charge::whereYear('due_date', now()->year)->sum('amount'),
            2
        );
    }

    #[Computed]
    public function expiringHostings()
    {
        return Project::with('client')
            ->where('status', 'active')
            ->whereNotNull('renewal_date')
            ->whereBetween('renewal_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
            ->orderBy('renewal_date')
            ->get();
    }

    #[Computed]
    public function expiringDomains()
    {
        return Project::with('client')
            ->where('status', 'active')
            ->whereNotNull('domain_expiry_date')
            ->whereBetween('domain_expiry_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
            ->orderBy('domain_expiry_date')
            ->get();
    }

    #[Computed]
    public function monthlyRevenue(): array
    {
        $start = now()->subMonths(11)->startOfMonth();

        $rows = Payment::selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as ym, SUM(amount) as total")
            ->where('payment_date', '>=', $start->toDateString())
            ->groupByRaw("DATE_FORMAT(payment_date, '%Y-%m')")
            ->pluck('total', 'ym');

        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $months[] = [
                'label'  => $m->format('M Y'),
                'amount' => (float) ($rows[$m->format('Y-m')] ?? 0),
            ];
        }
        return $months;
    }
};
?>

<div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

    {{-- Alerts bar --}}
    @php
        $hasAlerts = $this->overdueCharges->isNotEmpty()
                  || $this->expiringHostings->isNotEmpty()
                  || $this->expiringDomains->isNotEmpty();
    @endphp
    @if($hasAlerts)
        <div class="flex flex-col gap-2 mb-6">

            @if($this->overdueCharges->isNotEmpty())
                <a href="/charges"
                   class="flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                    <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <span class="text-sm font-medium text-red-700">
                        {{ $this->overdueCharges->count() }} {{ $this->overdueCharges->count() === 1 ? 'overdue charge' : 'overdue charges' }}
                        — €{{ number_format($this->overdueCharges->sum('amount'), 2) }} unpaid
                    </span>
                    <svg class="w-4 h-4 text-red-400 ml-auto" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            @endif

            @foreach($this->expiringHostings as $project)
                <a href="/projects/{{ $project->id }}/edit"
                   class="flex items-center gap-3 px-4 py-3 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition-colors">
                    <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6m-16.5-3a3 3 0 013-3h13.5a3 3 0 013 3m-19.5 0a4.5 4.5 0 01.9-2.7L5.737 5.1a3.375 3.375 0 012.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 01.9 2.7m0 0a3 3 0 01-3 3m0 3h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008zm-3 6h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008z" />
                    </svg>
                    <span class="text-sm font-medium text-amber-700">
                        Hosting renewal: <strong>{{ $project->name }}</strong> ({{ $project->client->name }})
                        — {{ $project->renewal_date->format('d M Y') }}
                        <span class="font-normal text-amber-600">({{ now()->diffInDays($project->renewal_date) }} days)</span>
                    </span>
                    <svg class="w-4 h-4 text-amber-400 ml-auto" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            @endforeach

            @foreach($this->expiringDomains as $project)
                <a href="/projects/{{ $project->id }}/edit"
                   class="flex items-center gap-3 px-4 py-3 bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100 transition-colors">
                    <svg class="w-4 h-4 text-purple-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253" />
                    </svg>
                    <span class="text-sm font-medium text-purple-700">
                        Domain expiry: <strong>{{ $project->domain ?? $project->name }}</strong> ({{ $project->client->name }})
                        — {{ $project->domain_expiry_date->format('d M Y') }}
                        <span class="font-normal text-purple-600">({{ now()->diffInDays($project->domain_expiry_date) }} days)</span>
                    </span>
                    <svg class="w-4 h-4 text-purple-400 ml-auto" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            @endforeach

        </div>
    @endif

    {{-- Summary cards --}}
    <div class="grid grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500 mb-1">Unpaid Charges</p>
            <p class="text-2xl font-bold text-red-600">€{{ $this->totalUnpaid }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500 mb-1">Overdue</p>
            <p class="text-2xl font-bold text-orange-600">{{ $this->overdueCharges->count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500 mb-1">Collected {{ now()->year }}</p>
            <p class="text-2xl font-bold text-green-600">€{{ $this->collectedThisYear }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500 mb-1">Invoiced {{ now()->year }}</p>
            <p class="text-2xl font-bold text-gray-900">€{{ $this->totalInvoicedThisYear }}</p>
        </div>
    </div>

    {{-- Revenue chart --}}
    @php
        $chartLabels  = json_encode(collect($this->monthlyRevenue)->pluck('label')->values()->toArray());
        $chartAmounts = json_encode(collect($this->monthlyRevenue)->pluck('amount')->values()->toArray());
    @endphp
    <div class="bg-white rounded-lg shadow p-6 mb-6" wire:ignore>
        <h2 class="text-base font-semibold text-gray-900 mb-4">Monthly Revenue — Last 12 Months</h2>
        <canvas id="revenueChart" height="80"
                data-labels="{{ $chartLabels }}"
                data-amounts="{{ $chartAmounts }}"></canvas>
    </div>
    <script>
        (function () {
            var el = document.getElementById('revenueChart');
            if (!el || !window.Chart) return;
            new window.Chart(el, {
                type: 'bar',
                data: {
                    labels:   JSON.parse(el.dataset.labels),
                    datasets: [{ label: 'Revenue (€)', data: JSON.parse(el.dataset.amounts), backgroundColor: 'rgba(37,99,235,0.8)', borderRadius: 4 }],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { callback: function(v){ return '€' + v.toLocaleString(); } } } },
                },
            });
        })();
    </script>

    <div class="grid grid-cols-2 gap-6">

        {{-- Overdue charges --}}
        <div>
            <h2 class="text-base font-semibold text-gray-900 mb-3">Overdue Charges</h2>
            @if($this->overdueCharges->isEmpty())
                <p class="text-sm text-gray-500 bg-white rounded-lg shadow p-4">No overdue charges.</p>
            @else
                <div class="bg-white rounded-lg shadow divide-y divide-gray-100">
                    @foreach($this->overdueCharges as $charge)
                        <div class="px-4 py-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $charge->title }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $charge->client->name }} &middot;
                                    Due {{ $charge->due_date->format('d M Y') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-red-600">€{{ number_format($charge->amount, 2) }}</p>
                                <a href="/charges/{{ $charge->id }}/edit"
                                   class="text-xs text-blue-600 hover:underline">Edit</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Recent clients --}}
        <div>
            <h2 class="text-base font-semibold text-gray-900 mb-3">Recent Clients</h2>
            @if($this->recentClients->isEmpty())
                <p class="text-sm text-gray-500 bg-white rounded-lg shadow p-4">No clients yet.</p>
            @else
                <div class="bg-white rounded-lg shadow divide-y divide-gray-100">
                    @foreach($this->recentClients as $client)
                        <div class="px-4 py-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $client->name }}</p>
                                <p class="text-xs text-gray-500">{{ $client->email ?? 'No email' }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium {{ $client->status->color() }}">
                                    {{ $client->status->label() }}
                                </span>
                                <a href="/clients/{{ $client->id }}"
                                   class="text-xs text-blue-600 hover:underline">View</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>
