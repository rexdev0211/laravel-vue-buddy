@if ($errors->any())
    <ul class="alert alert-danger">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

@if (isset($successMessage))
    <div class="alert alert-success alert-dismissible">
        {{ $successMessage }}
    </div>
@elseif(Session::has('successMessage'))
    <div class="alert alert-success alert-dismissible">
        {{ Session::get('successMessage') }}
    </div>
@endif
