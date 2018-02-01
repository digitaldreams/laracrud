<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{config('app.name')}}</title>
    <link rel="stylesheet" href="{{asset('laracrud/css/bulma.css')}}">
    <link href="{{ asset('laracrud/css/font-awesome.min.css') }}" rel="stylesheet">
    @section('styles')
</head>
<body>
<section class="section">
    @include('laracrud.includes.bulma.navbar')
    @include('laracrud.includes.bulma.alerts')

    <div class="container">
        <div class="navbar" role="navigation" aria-label="Breadcrumb">
            <div class="navbar-menu is-active " id="breadCrumbSection">
                <div class="navbar-start">
                    <div class="nav-item">
                        <nav class="breadcrumb" aria-label="breadcrumbs">
                            <ul>
                                <li>
                                    <a href="#">
                                        <span class="icon is-small"><i class="fa fa-home"></i></span>
                                        <span>Home</span>
                                    </a>
                                </li>
                                @section('breadcrumb')
                            </ul>
                        </nav>
                    </div>

                </div>
                <div class="navbar-end">
                    @section('tools')
                </div>
            </div>
        </div>

    </div>

    <div class="container">
        @section('content')
    </div>

    <footer class="footer">
        <div class="container">
            <div class="content has-text-centered">
                <p>
                    <strong><a href="https://github.com/digitaldreams">LaraCrud</a> </strong> by
                    <a href="https://tuhinbepari.com">Tuhin Bepari</a>.</a>
                </p>
            </div>
        </div>
    </footer>
</section>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function () {

        // Get all "navbar-burger" elements
        var $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);

        // Check if there are any navbar burgers
        if ($navbarBurgers.length > 0) {

            // Add a click event on each of them
            $navbarBurgers.forEach(function ($el) {
                $el.addEventListener('click', function () {

                    // Get the target from the "data-target" attribute
                    var target = $el.dataset.target;
                    var $target = document.getElementById(target);
                    // Toggle the class on both the "navbar-burger" and the "navbar-menu"
                    $el.classList.toggle('is-active');
                    $target.classList.toggle('is-active');
                });
            });
        }
    });
</script>
@section('scripts')
</body>
</html>