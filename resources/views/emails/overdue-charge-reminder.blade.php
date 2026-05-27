<x-mail::message>
# Ληξιπρόθεσμη Πληρωμή

Αγαπητέ/ή {{ $charge->client->name }},

Σας ενημερώνουμε ότι η παρακάτω υπηρεσία έληξε στις **{{ $charge->due_date->format('d/m/Y') }}** και η πληρωμή εκκρεμεί εδώ και **{{ $daysOverdue }} {{ $daysOverdue === 1 ? 'ημέρα' : 'ημέρες' }}**.

<x-mail::table>
| | |
|:--|:--|
| **Περιγραφή** | {{ $charge->title }} |
| **Ποσό** | €{{ number_format($charge->amount, 2) }} |
| **Ημερομηνία Λήξης** | {{ $charge->due_date->format('d/m/Y') }} |
| **Ημέρες Καθυστέρησης** | {{ $daysOverdue }} |
@if($charge->project)
| **Project** | {{ $charge->project->name }} |
@endif
</x-mail::table>

@if($charge->description)
*{{ $charge->description }}*
@endif

Παρακαλούμε να τακτοποιήσετε την πληρωμή το συντομότερο δυνατό. Αν έχετε ήδη πληρώσει, παρακαλούμε αγνοήστε αυτό το μήνυμα.

Για οποιαδήποτε απορία επικοινωνήστε μαζί μας στο {{ config('app.admin_email') }}

Με εκτίμηση,<br>
**Χάρης Βαλτζής**
</x-mail::message>
