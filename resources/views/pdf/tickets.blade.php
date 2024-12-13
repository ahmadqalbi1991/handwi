<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<style>
    .tickets-wrapper {
        width: 100%;
    }

    .ticket {
        width: 40%;
        display: inline-block;
        position: relative;
        padding: 1rem;
        border: 1px dashed;
        border-radius: 10px;
        margin: 7px;
        background: black;
        color: #fff;
        height: 185px;
    }

    .ticket-logo {
        width: 50px;
        position: absolute;
        top: 50px;
    }

    .ticket-logo .logo {
        width: 100%;
    }

    .ticket-logo img {
        width: 100%;
    }

    .ticket-details, .user-details {
        width: 65%;
        top: 20px;
        position: relative;
        left: 35%;
    }

    .ticket > div {
        display: inline-block;
    }

    .user-details {
        margin-top: 10px;
    }

    .user-details > div {
        float: right;
        top: -17px;
        position: relative;
    }

    h5 {
        font-size: 16px;
        margin: 0;
    }

    p {
        font-size: 14px;
        margin: 0;
    }

    .no-break {
        page-break-inside: avoid;
    }

</style>
<div class="tickets-wrapper">
    @php
    $img = public_path('images/logo.png')
    @endphp
        @foreach($tickets as $ticket)
            <div class="ticket no-break">
                <div class="ticket-logo no-break">
                    <div class="logo">
                        <img src="{{ $img }}" alt="logo">
                    </div>
                </div>
                <div class="ticket-details">
                    <h5>{{ $ticket->campaigns_title }}</h5>
                    <p><strong>Purchased on: {{ \Carbon\Carbon::parse($ticket->order_placed_date)->format('d/M/Y, h:i A') }}</strong></p>
                </div>
                <div class="user-details">
                    <p>
                        {{ $ticket->user_first_name . ' ' . $ticket->user_last_name }}
                    </p>
                    <div>
                        <p>Ticket Number</p>
                        <h5>{{ $ticket->draw_slip_number }}</h5>
                    </div>
                </div>
            </div>
        @endforeach
</div>
</body>
</html>