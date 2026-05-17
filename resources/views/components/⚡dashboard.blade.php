<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Charge;
use App\Models\Client;
use App\Models\Payment;

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
