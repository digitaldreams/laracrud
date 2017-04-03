@section('messages')
    @if(Session::has('app_error') 
    || Session::has('app_message')
    || Session::has('app_info')
    || Session::has('app_warning'))
    <!-- check for flash error message -->
        @if(Session::has('app_error'))
        <div class="alert alert-danger alert-dismissable" role="alert" id="app_error">{{ Session::get('app_error') }}</div>
        @endif
        <!-- check for flash message -->
        @if(Session::has('app_message'))
        <div class="alert alert-success alert-dismissable" role="alert" id="app_message">{{ Session::get('app_message') }}</div>
        @endif
        <!-- check for flash warning message -->
        @if(Session::has('app_warning'))
        <div class="alert alert-warning alert-dismissable" role="alert" id="app_warning">
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
            </button>
            {{ Session::get('app_warning') }}
        </div>
        @endif<!-- check for flash info message -->
        @if(Session::has('app_info'))
        <div class="alert alert-info alert-dismissable" role="alert" id="app_info">
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
            </button>
            {{ Session::get('app_info') }}
        </div>
        @endif
    @endif

   
@show