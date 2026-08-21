@if (session('success'))
    <div class="fi-alert fi-alert-ok">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="fi-alert fi-alert-err">{{ session('error') }}</div>
@endif

@if ($errors->any())
    <div class="fi-alert fi-alert-err">
        <ul class="fi-list">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
