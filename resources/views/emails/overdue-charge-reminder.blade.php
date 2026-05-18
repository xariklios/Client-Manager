<x-mail::message>
# Overdue Payment Notice

Γεια σου, {{ $charge->client->name }},

This is a notice that the following payment is **{{ $daysOverdue }} {{ $daysOverdue === 1 ? 'day' : 'days' }} overdue**.

<x-mail::table>
| | |
|:--|:--|
| **Description** | {{ $charge->title }} |
| **Amount** | €{{ number_format($charge->amount, 2) }} |
| **Due Date** | {{ $charge->due_date->format('d F Y') }} |
| **Days Overdue** | {{ $daysOverdue }} |
@if($charge->project)
| **Project** | {{ $charge->project->name }} |
@endif
</x-mail::table>

@if($charge->description)
*{{ $charge->description }}*
@endif

Please settle this payment as soon as possible. If you have already paid, please ignore this message.

For any questions you can reach us at [{{ config('app.admin_email') }}](mailto:{{ config('app.admin_email') }}).

Ευχαριστώ,<br>
**Charis Valtzis**
</x-mail::message>
