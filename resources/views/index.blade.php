<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
</head>
<body>
    <div class="messages">
        <div class="top">
            <div>
                <p>Ross Edlin</p>
                <small>Online</small>
            </div>
        </div>
        <div class="message">
            @include('receive', ['message' => 'Hello there. How are you doing?'])
        </div>
        <div class="bottom">
            <form>
                <input type="text" id="message" name="message" placeholder="Enter message..." autocomplete="off">
                <button type="submit">Send</button>
            </form>
        </div>
    </div>
</body>
<script>
    const pusher = new Pusher("{{ config('broadcasting.connections.pusher.key') }}", {cluster: 'eu'});
    const channel = pusher.subscribe('public');
    console.log("{{ config('broadcasting.connections.pusher.key') }}");

    channel.bind('chat', function (data) {
        console.log('Event fired and received from Pusher.');
        console.log(data.message);
        $.post("/receive", {
            _token: '{{ csrf_token() }}',
            message: data.message,
        })
            .done(function (res) {
                $(".messages > .message").last().after(res);
                $(document).scrollTop($(document).height());
            });
    });

    $("form").submit(function (event) {
        event.preventDefault();

        $.ajax({
            url: "/broadcast",
            method: "POST",
            headers: {
                'X-Socket-Id' : pusher.connection.socket_id
            },
            data: {
                _token: '{{ csrf_token() }}',
                message: $("form input[name='message']").val(),
            }
        }).done(function (res) {
            $(".messages > .message").last().after(res);
            $("form input[name='message']").val('');
            $(document).scrollTop($(document).height());
        });
    });
</script>
</html>