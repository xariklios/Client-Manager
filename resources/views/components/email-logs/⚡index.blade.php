<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\EmailLog;

new class extends Component
{
    #[Computed]
    public function logs()
    {
        return EmailLog::latest()->get();
    }
};
?>

<div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Email Log</h1>

    @if($this->logs->isEmpty())
        <p class="text-gray-500 text-sm">No emails sent yet. Run a reminder command to generate one.</p>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Sent At</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Type</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Recipient</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Subject</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Records</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($this->logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-600">
                                {{ $log->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    {{ $log->type === 'overdue_charges' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ str_replace('_', ' ', $log->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $log->recipient }}</td>
                            <td class="px-4 py-3 text-gray-900">{{ $log->subject }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ $log->records_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <p class="text-xs text-gray-400 mt-4">
        Emails are sent to <strong>{{ config('app.admin_email') }}</strong>.
        During development, they are written to <code>storage/logs/laravel.log</code> instead of actually sent.
    </p>
</div>
