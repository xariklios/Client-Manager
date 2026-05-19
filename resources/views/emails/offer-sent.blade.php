<x-mail::message>
Dear {{ $offer->client->name }},

Please find attached our offer for **{{ $offer->title }}**.

The PDF contains all the details including scope of work and pricing.

If you have any questions, feel free to reach us at [{{ config('app.admin_email') }}](mailto:{{ config('app.admin_email') }}).

Thanks,<br>
**Charis Valtzis**
</x-mail::message>
