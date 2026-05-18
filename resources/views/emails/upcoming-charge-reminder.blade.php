<x-mail::message>
# Payment Reminder

Γεια σου, {{ $charge->client->name }},

This is a friendly reminder that the following payment is due in **{{ $daysUntilDue }} {{ $daysUntilDue === 1 ? 'day' : 'days' }}**.

<x-mail::table>
| | |
|:--|:--|
| **Description** | {{ $charge->title }} |
| **Amount** | €{{ number_format($charge->amount, 2) }} |
| **Due Date** | {{ $charge->due_date->format('d F Y') }} |
@if($charge->project)
| **Project** | {{ $charge->project->name }} |
@endif
</x-mail::table>

@if($charge->description)
*{{ $charge->description }}*
@endif

Please arrange payment before the due date to avoid any service interruption.

If you have already made this payment, please disregard this message.

For any questions you can reach us at [{{ config('app.admin_email') }}](mailto:{{ config('app.admin_email') }}).

Ευχαριστώ,<br>
**Charis Valtzis**
</x-mail::message>
