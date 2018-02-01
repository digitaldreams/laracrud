<?php if (!empty(session('app_error'))): ?>
<div class="message is-danger">
    <div class="message-body">
        {{ session('app_error') }}
    </div>
</div>
<?php endif; ?>

@if(empty(session('app_error')) && !empty(session('app_message')))
    <div class="message is-success">
        <div class="message-body">
            {{ session('app_message') }}
        </div>
    </div>
@endif

<?php if (!empty(session('status'))): ?>
<div class="message is-light">
    <div class="message-body">
        {{ session('status') }}
    </div>
</div>
<?php endif; ?>

<!-- check for flash warning message -->
@if(!empty(session('app_warning')))
    <div class="message is-warning">
        <div class="message-body">
            {{ session('app_warning') }}
        </div>
    </div>
@endif<!-- check for flash info message -->
@if(!empty(session('app_info')))
    <div class="message is-info">
        <div class="message-body">
            {{ session('app_info') }}
        </div>
    </div>

@endif

