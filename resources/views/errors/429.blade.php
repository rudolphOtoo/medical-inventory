@extends('errors.layout')

@section('title', __('Too Many Requests'))
@section('code', '429 - RATE LIMITED')

@section('icon')
    <x-ui.icon name="shield" class="size-8 text-rose-400" />
@endsection

@section('heading', __('Sign-In Access Temporarily Locked'))

@section('message')
    {{ __('Too many failed authentication attempts were detected for this station. Access has been throttled to protect the network.') }}
    <span
        id="retry-countdown"
        class="mt-2 block font-mono text-rose-600 dark:text-rose-400"
        aria-live="polite"
        role="timer"
    ></span>
@endsection

@push('scripts')
    @php
        $retryAfter = ($exception ?? null)?->getHeaders() ?? [];
        $retryAfter = $retryAfter['Retry-After'] ?? $retryAfter['retry-after'] ?? null;
    @endphp
    @if ($retryAfter !== null)
        <script>
            (function () {
                const el = document.getElementById('retry-countdown');
                if (! el) return;

                const template = @json(__('Try again in :seconds seconds.', ['seconds' => '{seconds}']));
                const initial = parseInt(@json($retryAfter), 10);
                if (! Number.isFinite(initial) || initial < 1) return;

                let seconds = Math.max(1, initial);

                const render = () => {
                    el.textContent = template.replace('{seconds}', seconds);
                };

                render();

                const timer = setInterval(() => {
                    seconds -= 1;

                    if (seconds <= 0) {
                        clearInterval(timer);
                        window.location.reload();

                        return;
                    }

                    render();
                }, 1000);
            })();
        </script>
    @endif
@endpush