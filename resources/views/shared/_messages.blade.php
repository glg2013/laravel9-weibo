@foreach(['danger', 'warning', 'success', 'info'] as $msgType)
    @if(session()->has($msgType))
        <div class="flash-message">
          <p class="alert alert-{{ $msgType }}">
            {{ session()->get($msgType) }}
          </p>
        </div>
    @endif
@endforeach
