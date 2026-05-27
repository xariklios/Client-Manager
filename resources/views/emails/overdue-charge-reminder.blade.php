<x-mail::message>
# Ληξιπρόθεσμη Πληρωμή

Αγαπητέ/ή {{ $charge->client->name }},

Σας ενημερώνουμε ότι η παρακάτω πληρωμή είναι **ληξιπρόθεσμη κατά {{ $daysOverdue }} {{ $daysOverdue === 1 ? 'ημέρα' : 'ημέρες' }}**.

<x-mail::table>
| | |
|:--|:--|
| **Περιγραφή** | {{ $charge->title }} |
| **Ποσό** | €{{ number_format($charge->amount, 2) }} |
| **Ημερομηνία Λήξης** | {{ $charge->due_date->format('d F Y') }} |
| **Ημέρες Καθυστέρησης** | {{ $daysOverdue }} |
@if($charge->project)
| **Project** | {{ $charge->project->name }} |
@endif
</x-mail::table>

@if($charge->description)
*{{ $charge->description }}*
@endif

Παρακαλούμε να τακτοποιήσετε την πληρωμή το συντομότερο δυνατό. Αν έχετε ήδη πληρώσει, παρακαλούμε αγνοήστε αυτό το μήνυμα.

Για οποιαδήποτε απορία μπορείτε να επικοινωνήσετε μαζί μας στο [{{ config('app.admin_email') }}](mailto:{{ config('app.admin_email') }}).

Με εκτίμηση,<br>
**Charis Valtzis**
</x-mail::message>
