@extends('shop::layouts.master')

@section('content')
<div class="container text-center my-5">
    <h2 class="mb-3">Finaliza tu pago con Bold</h2>
    <p class="text-muted">Confirma el pago sin salir de la tienda.</p>

    <div class="d-flex justify-content-center">
        <button id="bold-custom-button" class="btn btn-primary px-4 py-3" type="button" disabled>
            {{ $buttonLabel }}
            @if($amount > 0)
                <span class="ms-2">{{ $currency }} {{ number_format($amount, 0, ',', '.') }}</span>
            @endif
        </button>
    </div>

    <p id="bold-status" class="small text-muted mt-3">Cargando Bold…</p>

    <script>
        (() => {
            const config = @json($config);
            const button = document.getElementById('bold-custom-button');
            const status = document.getElementById('bold-status');
            const boldSrc = 'https://checkout.bold.co/library/boldPaymentButton.js';

            const setStatus = (text, isError = false) => {
                status.textContent = text;
                status.classList.toggle('text-danger', isError);
            };

            const initBoldCheckout = () => {
                if (document.querySelector(`script[src="${boldSrc}"]`)) {
                    return;
                }

                const js = document.createElement('script');

                js.onload = () => {
                    window.dispatchEvent(new Event('boldCheckoutLoaded'));
                };

                js.onerror = () => {
                    window.dispatchEvent(new Event('boldCheckoutLoadFailed'));
                };

                js.src = boldSrc;
                document.head.appendChild(js);
            };

            const setupButton = () => {
                if (! window.BoldCheckout) {
                    return;
                }

                const checkout = new window.BoldCheckout(config);

                button.disabled = false;
                setStatus('Listo para pagar');

                const openCheckout = (event) => {
                    event?.preventDefault();
                    checkout.open();
                };

                button.addEventListener('click', openCheckout);
            };

            window.addEventListener('boldCheckoutLoaded', setupButton, { once: true });

            window.addEventListener('boldCheckoutLoadFailed', () => {
                button.disabled = false;
                setStatus('No pudimos cargar Bold. Reintenta en unos segundos.', true);
            });

            if (window.BoldCheckout) {
                setupButton();
            } else {
                initBoldCheckout();
            }
        })();
    </script>
</div>
@endsection
