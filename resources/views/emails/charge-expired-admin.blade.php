<x-mail::message>
# Λήξη Υπηρεσίας Σήμερα

Η παρακάτω υπηρεσία έληξε σήμερα **({{ $charge->due_date->format('d/m/Y') }})** και δεν έχει καταχωρηθεί πληρωμή.

<x-mail::table>
| | |
|:--|:--|
| **Πελάτης** | {{ $charge->client->name }} |
| **Υπηρεσία** | {{ $charge->title }} |
| **Ποσό** | €{{ number_format($charge->amount, 2) }} |
@if($charge->project)
| **Project** | {{ $charge->project->name }} |
@endif
</x-mail::table>

Με εκτίμηση,<br>
**Χάρης Βαλτζής**
</x-mail::message>
