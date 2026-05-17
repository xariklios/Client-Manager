<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\RecurringCharge;

new class extends Component
{
    #[Computed]
    public function recurringCharges()
    {
        return RecurringCharge::with(['client', 'project'])
            ->orderBy('due_month')
            ->orderBy('due_day')
            ->get();
    }
};
?>

<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Recurring Charges</h1>
            <p class="text-sm text-gray-500 mt-1">Annual payments generated automatically 60 days before due date.</p>
        </div>
        <a href="/recurring/create"
           class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
            Add Recurring
        </a>
    </div>

    @if($this->recurringCharges->isEmpty())
        <p class="text-gray-500 text-sm">No recurring charges set up yet.</p>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Client</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Category</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Title</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Due Date</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Recurrence</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Next Due</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Amount</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Notify Client</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($this->recurringCharges as $rc)
                        <tr class="hover:bg-gray-50 {{ ! $rc->active ? 'opacity-50' : '' }}">
                            <td class="px-4 py-3 font-medium text-gray-900">
                                <a href="/clients/{{ $rc->client_id }}" class="hover:underline">
                                    {{ $rc->client->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium {{ $rc->category->color() }}">
                                    {{ $rc->category->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-900">{{ $rc->title }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $rc->dueDateLabel() }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $rc->intervalLabel() }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                @if($rc->active)
                                    {{ $rc->nextDueDate()->format('d M Y') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="font-medium text-gray-900">€{{ number_format($rc->amount, 2) }}</span>
                                @if($rc->category->value === 'bundle' && $rc->bundle_items)
                                    <div class="text-xs text-gray-400 mt-0.5 space-y-0.5">
                                        @foreach($rc->bundle_items as $item)
                                            <div>{{ $item['label'] }}: €{{ number_format((float)$item['amount'], 2) }}</div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($rc->notify_client)
                                    <span class="text-green-600 text-xs font-medium">Yes</span>
                                @else
                                    <span class="text-gray-400 text-xs">No</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($rc->active)
                                    <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Active</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="/recurring/{{ $rc->id }}"
                                   class="text-blue-600 hover:underline text-xs">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
