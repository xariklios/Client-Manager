<x-mail::message>
# Υπενθύμιση Ανανέωσης

Αγαπητέ/ή {{ $charge->client->name }},

Σας υπενθυμίζουμε ότι η παρακάτω υπηρεσία λήγει στις **{{ $charge->due_date->format('d/m/Y') }}**.

<x-mail::table>
| | |
|:--|:--|
| **Περιγραφή** | {{ $charge->title }} |
| **Ποσό** | €{{ number_format($charge->amount, 2) }} |
| **Ημερομηνία Λήξης** | {{ $charge->due_date->format('d/m/Y') }} |
@if($charge->project)
| **Project** | {{ $charge->project->name }} |
@endif
</x-mail::table>

@if($charge->description)
*{{ $charge->description }}*
@endif

Παρακαλούμε να τακτοποιήσετε την ανανέωση πριν την ημερομηνία λήξης για να αποφύγετε οποιαδήποτε διακοπή υπηρεσίας.

Αν έχετε ήδη πραγματοποιήσει αυτή την πληρωμή, παρακαλούμε αγνοήστε αυτό το μήνυμα.

Για οποιαδήποτε απορία επικοινωνήστε μαζί μας στο {{ config('app.admin_email') }}

Με εκτίμηση,<br>
**Χάρης Βαλτζής**
</x-mail::message>
