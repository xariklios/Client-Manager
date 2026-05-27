<x-mail::message>
# Υπενθύμιση Πληρωμής

Αγαπητέ/ή {{ $charge->client->name }},

Σας υπενθυμίζουμε ότι η παρακάτω πληρωμή εκκρεμεί σε **{{ $daysUntilDue }} {{ $daysUntilDue === 1 ? 'ημέρα' : 'ημέρες' }}**.

<x-mail::table>
| | |
|:--|:--|
| **Περιγραφή** | {{ $charge->title }} |
| **Ποσό** | €{{ number_format($charge->amount, 2) }} |
| **Ημερομηνία Λήξης** | {{ $charge->due_date->format('d F Y') }} |
@if($charge->project)
| **Project** | {{ $charge->project->name }} |
@endif
</x-mail::table>

@if($charge->description)
*{{ $charge->description }}*
@endif

Παρακαλούμε να τακτοποιήσετε την πληρωμή πριν την ημερομηνία λήξης για να αποφύγετε οποιαδήποτε διακοπή υπηρεσίας.

Αν έχετε ήδη πραγματοποιήσει αυτή την πληρωμή, παρακαλούμε αγνοήστε αυτό το μήνυμα.

Για οποιαδήποτε απορία μπορείτε να επικοινωνήσετε μαζί μας στο [{{ config('app.admin_email') }}](mailto:{{ config('app.admin_email') }}).

Με εκτίμηση,<br>
**Charis Valtzis**
</x-mail::message>
