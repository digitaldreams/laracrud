<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('laracrud/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('laracrud/css/font-awesome.min.css') }}" rel="stylesheet">
    @yield('styles')
    <script>
        window.Laravel = <?php echo json_encode([
            'csrfToken' => csrf_token(),
        ]); ?>
    </script>
</head>

<body>
<div id="app">
    @include('laracrud.includes.navbar')
    @if(auth()->check())
        <nav aria-label="breadcrumb" role="navigation" class="container">
            <ol class="breadcrumb mb-1" style="font-size: 14px;font-weight: 400;margin: auto">
                <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                @yield('breadcrumb')
                @if(!empty(request('q')))
                    <li class="breadcrumb-item">
                        <span class="text-muted">Showing result with keyword <b>{{request('q')}}</b></span>
                        <button class="btn btn-none p-0 m-0" id="searchCleaner"><i
                                    class="text-danger fa fa-remove"></i>
                        </button>
                    </li>
                @endif
                <li class="pull-right">@yield('tools')</li>
            </ol>
        </nav>
    @endif

    <div class="container">
        @include('laracrud.includes.alerts')
        @yield('content')
    </div>
</div>

<!-- Scripts -->

<script src="{{ asset('laracrud/js/jquery-3.2.1.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('laracrud/js/bootstrap.bundle.min.js') }}" type="text/javascript"></script>

<script type="text/javascript">
    $("#searchCleaner").on('click', function (e) {
        e.preventDefault();
        $("input[name=q]").val('');
        $("#searchForm").submit();
    });
</script>

<script type="text/javascript">
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>

@yield('scripts')
</body>
</html>
