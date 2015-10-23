<!DOCTYPE html>
<html>
    <head>
        @include('includes.head')
    </head>
    <body>
        <div class="row full-width">
                @include('includes.header')
        </div>
        <div class="container">

            @yield('content')

        </div>
        @include('includes.footer')

        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
        <script src="{{ URL::to('js/scripts.js') }}"></script>
    </body>
</html>
