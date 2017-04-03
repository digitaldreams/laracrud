@include('layouts.admin.partials.header')
@include('layouts.admin.partials.sidebar')

        <!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">


    <!-- Main content -->
    <section class="content">
        @include('layouts.admin.partials.alert')
        @yield('content')

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

@include('layouts.admin.partials.footer')
        <!-- jQuery 2.2.3 -->

<script src="{{ asset('js/manifest.js') }}"></script>
<script src="{{ asset('js/vendor.js') }}"></script>
<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/adminlte.js') }}"></script>


<script type="text/javascript">
    $(".select2").select2();
</script>
@yield('scripts')
