<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>
body{
    font-family: monospace;
    width:280px;
}

.ticket{
    text-align:center;
}

hr{
    border-top:1px dashed black;
}
</style>

</head>

<body>

<div class="ticket">

<h3>MAIRIE {{ $ticket->commune->nom }}</h3>

<hr>

<p>Ticket N°</p>
<strong>{{ $ticket->numero_ticket }}</strong>

<hr>

<p><strong>Contribuable</strong></p>
{{ $ticket->contribuable->nom }}

<hr>

<p><strong>Taxe</strong></p>
{{ $ticket->taxe->typeTaxe->nom }}

<p><strong>Montant</strong></p>
{{ number_format($ticket->taxe->montant,0,',',' ') }} FCFA

<hr>

<p><strong>Statut</strong></p>
{{ $ticket->statut }}

<hr>

<p><strong>Date</strong></p>
{{ $ticket->created_at->format('d/m/Y H:i') }}

<p><strong>Expiration</strong></p>
{{ $ticket->date_expiration }}

<hr>

{!! QrCode::size(120)->generate($ticket->qr_hash) !!}

<br>

<p>Merci de votre paiement</p>

</div>

<script>
window.print();
</script>

</body>
</html>