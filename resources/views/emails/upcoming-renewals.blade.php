<x-mail::message>
# Upcoming Renewals

You have **{{ $projects->count() }}** project {{ Str::plural('renewal', $projects->count()) }} coming up in the next **{{ $withinDays }} days**.

<x-mail::table>
| Client | Project | Domain | Renewal Date | Days Left |
|--------|---------|--------|--------------|-----------|
@foreach($projects as $project)
| {{ $project->client->name }} | {{ $project->name }} | {{ $project->domain ?? '—' }} | {{ $project->renewal_date->format('d M Y') }} | {{ now()->diffInDays($project->renewal_date) }}d |
@endforeach
</x-mail::table>

<x-mail::button :url="config('app.url') . '/clients'">
View Clients
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
