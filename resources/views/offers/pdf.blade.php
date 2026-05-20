<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #1a1a1a; background: #fff; }
        .page { padding: 48px 52px; }

        /* Header */
        .header { display: table; width: 100%; margin-bottom: 36px; }
        .header-left  { display: table-cell; vertical-align: top; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }
        .from-name    { font-size: 18px; font-weight: bold; color: #111; }
        .from-email   { font-size: 12px; color: #888; margin-top: 3px; }
        .offer-label  { font-size: 26px; font-weight: bold; color: #111; }
        .offer-meta   { font-size: 12px; color: #888; margin-top: 5px; line-height: 1.7; }

        hr { border: none; border-top: 1px solid #e5e7eb; margin: 24px 0; }

        /* Bill-to row */
        .meta-row  { display: table; width: 100%; margin-bottom: 28px; }
        .meta-cell { display: table-cell; vertical-align: top; width: 50%; }
        .meta-lbl  { font-size: 10px; text-transform: uppercase; letter-spacing: 0.8px; color: #9ca3af; font-weight: bold; margin-bottom: 5px; }
        .meta-val  { font-size: 13px; color: #111; line-height: 1.65; }
        .muted     { color: #6b7280; }

        /* Offer title */
        .offer-title { font-size: 18px; font-weight: bold; color: #111; margin-bottom: 18px; }

        /* Description content */
        .description { font-size: 13px; color: #374151; line-height: 1.75; margin-bottom: 32px; }

        /* Price block */
        .price-block { border-top: 2px solid #111; padding-top: 18px; }
        .price-row   { display: table; width: 100%; }
        .price-spc   { display: table-cell; width: 55%; }
        .price-tbl   { display: table-cell; width: 45%; }

        .price-line       { display: table; width: 100%; padding: 5px 0; }
        .price-line-lbl   { display: table-cell; color: #6b7280; font-size: 12px; }
        .price-line-val   { display: table-cell; text-align: right; font-size: 12px; color: #374151; }
        .price-original   { text-decoration: line-through; color: #d1d5db; }
        .price-total      { background: #111; padding: 8px 10px; margin-top: 4px; }
        .price-total .price-line-lbl,
        .price-total .price-line-val { color: #fff; font-weight: bold; font-size: 14px; }

        /* Footer */
        .footer { margin-top: 48px; border-top: 1px solid #f3f4f6; padding-top: 14px; text-align: center; font-size: 11px; color: #d1d5db; }
    </style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            <div class="from-name">{{ config('app.sender_name') }}</div>
            <div class="from-email">{{ config('app.admin_email') }}</div>
        </div>
        <div class="header-right">
            <div class="offer-label">Offer</div>
            <div class="offer-meta">
                #{{ str_pad($offer->id, 4, '0', STR_PAD_LEFT) }}<br>
                @if($offer->sent_date) Sent: {{ $offer->sent_date->format('d M Y') }}<br> @endif
                Date: {{ now()->format('d M Y') }}
            </div>
        </div>
    </div>

    <hr>

    {{-- Client + Offer info --}}
    <div class="meta-row">
        <div class="meta-cell">
            <div class="meta-lbl">Bill To</div>
            <div class="meta-val">
                <strong>{{ $offer->client->name }}</strong><br>
                @if($offer->client->contact_person) <span class="muted">{{ $offer->client->contact_person }}</span><br> @endif
                @if($offer->client->email)           <span class="muted">{{ $offer->client->email }}</span><br> @endif
                @if($offer->client->vat_number)      <span class="muted">VAT: {{ $offer->client->vat_number }}</span><br> @endif
                @if($offer->client->address)         <span class="muted">{{ $offer->client->address }}</span> @endif
            </div>
        </div>
        <div class="meta-cell">
            <div class="meta-lbl">Details</div>
            <div class="meta-val">
                @if($offer->project) <span class="muted">Project: {{ $offer->project->name }}</span><br> @endif
                <span class="muted">Status: {{ $offer->status->label() }}</span>
            </div>
        </div>
    </div>

    {{-- Offer title --}}
    <div class="offer-title">{{ $offer->title }}</div>

    {{-- Description (scope of work) --}}
    @if($offer->description)
        @php
            $descHtml = nl2br(preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', e($offer->description)));
        @endphp
        <div class="description">{!! $descHtml !!}</div>
    @endif

    {{-- Price block --}}
    @php
        $net   = (float) $offer->total;
        $vat   = round($net * $offer->vat_rate / 100, 2);
        $gross = $net + $vat;
    @endphp
    <div class="price-block">
        <div class="price-row">
            <div class="price-spc"></div>
            <div class="price-tbl">

                @if($offer->original_price)
                    <div class="price-line">
                        <div class="price-line-lbl">Original price</div>
                        <div class="price-line-val price-original">€{{ number_format((float)$offer->original_price, 2) }}</div>
                    </div>
                @endif

                <div class="price-line">
                    <div class="price-line-lbl">{{ $offer->original_price ? 'Offer price (net)' : 'Price (net)' }}</div>
                    <div class="price-line-val">€{{ number_format($net, 2) }}</div>
                </div>

                @if($offer->vat_rate > 0)
                    <div class="price-line">
                        <div class="price-line-lbl">VAT {{ $offer->vat_rate }}%</div>
                        <div class="price-line-val">€{{ number_format($vat, 2) }}</div>
                    </div>
                @endif

                <div class="price-total price-line">
                    <div class="price-line-lbl">Total{{ $offer->vat_rate == 0 ? ' (VAT exempt)' : '' }}</div>
                    <div class="price-line-val">€{{ number_format($gross, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    @if($offer->notes)
        @php
            $notesHtml = nl2br(preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', e($offer->notes)));
        @endphp
        <div style="margin-top:28px; font-size:11px; color:#9ca3af;">
            <strong style="text-transform:uppercase;letter-spacing:0.6px;">Notes</strong><br>
            <span style="color:#6b7280;">{!! $notesHtml !!}</span>
        </div>
    @endif

    <div class="footer">{{ config('app.sender_name') }} &middot; {{ config('app.admin_email') }} &middot; {{ config('app.sender_url') }}</div>

</div>
</body>
</html>
