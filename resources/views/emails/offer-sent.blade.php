<x-mail::message>
Γεια σου, {{ $offer->client->name }},

Ετοιμάσαμε μια προσφορά για **{{ $offer->title }}**.

Θα βρεις όλες τις λεπτομέρειες στο συνημμένο PDF.

Αν έχεις οποιαδήποτε απορία, μπορείς να επικοινωνήσεις μαζί μας στο [{{ config('app.admin_email') }}](mailto:{{ config('app.admin_email') }}).

Ευχαριστώ,<br>
**Charis Valtzis**
</x-mail::message>
