<x-mail::message>
Dear {{ $client->name }},

{{ $body }}

---

Thanks,<br>
**{{ config('app.sender_name') }}**<br>
[{{ config('app.sender_url') }}]({{ config('app.sender_url') }})
</x-mail::message>
