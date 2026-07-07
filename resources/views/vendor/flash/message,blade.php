@if (session()->has('flash_notification.message'))
<div role="alert" class="alert alert-{{ session('flash_notification.level') }}">
    {{ session('flash_notification.message') }}
</div>
@endif