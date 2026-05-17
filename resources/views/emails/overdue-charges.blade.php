<x-mail::message>
# Overdue Charges

You have **{{ $charges->count() }}** overdue {{ Str::plural('charge', $charges->count()) }} totalling **€{{ number_format($charges->sum('amount'), 2) }}**.

<x-mail::table>
| Client | Title | Due Date | Amount | Days Overdue |
|--------|-------|----------|--------|--------------|
@foreach($charges as $charge)
| {{ $charge->client->name }} | {{ $charge->title }} | {{ $charge->due_date->format('d M Y') }} | €{{ number_format($charge->amount, 2) }} | {{ $charge->due_date->diffInDays(now()) }}d |
@endforeach
</x-mail::table>

<x-mail::button :url="config('app.url') . '/charges?filter=overdue'">
View Overdue Charges
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
