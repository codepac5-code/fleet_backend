<link rel="shortcut icon" class="favicon_preview" href="{{ getDashboardLogo() }}" />
<link rel='stylesheet' href="{{ asset('vendor/fullcalendar/core/main.css')}}" />
<link rel='stylesheet' href="{{ asset('vendor/fullcalendar/daygrid/main.css')}}" />
<link rel='stylesheet' href="{{ asset('vendor/fullcalendar/timegrid/main.css')}}" />
<link rel='stylesheet' href="{{ asset('vendor/fullcalendar/list/main.css')}}" />
<link rel="stylesheet" href="{{ asset('css/backend-plugin.min.css')}}">
<link rel="stylesheet" href="{{ asset('css/backend.css?v=1.0.0')}}">
<link rel="stylesheet" href="{{ asset('vendor/@fortawesome/fontawesome-free/css/all.min.css')}}">
<link rel="stylesheet" href="{{ asset('vendor/line-awesome/dist/line-awesome/css/line-awesome.min.css')}}">
<link rel="stylesheet" href="{{ asset('vendor/remixicon/fonts/remixicon.css')}}">
<link rel="stylesheet" href="{{ asset('vendor/confirmJs/jquery-confirm.css')}}">
<!-- <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css')}}"> -->
<link rel="stylesheet" href="{{ asset('css/themes/select2.min.css')}}">
<link rel="stylesheet" href="{{ asset('vendor/magnific-popup/magnific-popup.css') }}">
<link rel="stylesheet" href="{{ asset('css/custom.css')}}">
<link rel="stylesheet" href="{{ asset('css/provide.css')}}">

<link href="https://fonts.googleapis.com/css2?family=Mulish:wght@700&display=swap" rel="stylesheet">

<script src="{{ asset('js/socket.io.min.js') }}"></script>


<script>

    const socket = io('http://127.0.0.1:3000' , {
        transports: ['websocket'],
        reconnectionAttempts: 5,
            timeout: 20000

    });//'http://127.0.0.1:3000'
    window.io = io;
    console.log(window.io);

    socket.on('connect', () => {
        console.log('Connected to server');
    });





    socket.on('public-channel:custom.event', (data) => {
        console.log('Received message:', data);
        document.getElementById('messages').innerHTML += `<p>${data}</p>`;
    });

    function sendMessage() {
        const message = 'Hello, Server!';
        socket.emit('emit-to-channel', {
            channel: 'public-channel',
            event: 'custom.event',
            data: message
        });
    }



    const socket_notification = io('http://127.0.0.1:4000' , {
        transports: ['websocket'],
        reconnectionAttempts: 5,
            timeout: 20000

    });//'http://127.0.0.1:3000'

    socket_notification.on('connect', () => {
        console.log('Connected to notification server');
    });

</script>

@if (auth()->user()->hasAnyRole(['super-admin']))
<script> 
    socket.emit('subscribe' ,'admins');
    socket_notification.emit('subscribe' ,'public-notification-super-admin');
</script>    
@endif

@if (auth()->user()->hasAnyRole(['office']))
<script> 
    socket.emit('subscribe' ,'offices');
    console.log('join to offices room');
</script>    
@endif

<!-- @if(session()->get('dir') == 'rtl')
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
@endif -->