<!DOCTYPE html>
<html>
    <head>
        @include('layouts.includes.head')
    </head>
    <body>

        @yield('content')
       
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
        <script src="{{ URL::to('js/scripts.js') }}"></script>
    </body>
</html>
