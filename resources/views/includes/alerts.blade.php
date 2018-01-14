<?php if (!empty(session('permit_error'))): ?>
<div class="alert alert-danger alert-dismissable p-2 px-4" role="alert"
     id="app_error">{{ session('permit_error') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>
        <!-- check for flash message -->
@if(empty(session('permit_error')) && !empty(session('permit_message')))
    <div class="alert alert-success alert-dismissable p-2 px-4" role="alert"
         id="app_message">{{ session('permit_message') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<?php if (!empty(session('status'))): ?>
<div class="alert alert-info alert-dismissable p-2 px-4" role="alert" id="app_error">{{ session('status') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>
        <!-- check for flash warning message -->
@if(!empty(session('permit_warning')))
    <div class="alert alert-warning alert-dismissable p-2 px-4" role="alert" id="app_warning">
        <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
        </button>
        {{ session('permit_warning') }}
    </div>
    @endif<!-- check for flash info message -->
    @if(!empty(session('permit_info')))
        <div class="alert alert-info alert-dismissable p-2 px-4" role="alert" id="app_info">
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
            </button>
            {{ session('permit_info') }}
        </div>
    @endif

