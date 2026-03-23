<x-shop::layouts :has-feature="false">
    <x-slot:title>
        Finaliza tu pago con Bold
    </x-slot>

    <div class="max-w-3xl mx-auto px-6 py-10 text-center">
        <h2 class="text-2xl font-semibold mb-2">Finaliza tu pago con Bold</h2>
        <p class="text-gray-600 mb-6">Confirma el pago sin salir de la tienda.</p>

        <button
            id="bold-custom-button"
            type="button"
            style="background:#1d4ed8;color:#fff;padding:12px 20px;border-radius:8px;min-width:220px;font-weight:600;box-shadow:0 8px 16px rgba(0,0,0,0.08);"
            class="inline-flex items-center justify-center text-base disabled:opacity-50 disabled:cursor-not-allowed"
        >
            {{ $buttonLabel }}
            @if($amount > 0)
                <span class="ml-2">{{ $currency }} {{ number_format($amount, 0, ',', '.') }}</span>
            @endif
        </button>

        <p class="text-sm text-gray-600 mt-3">Si no ves la ventana de pago, revisa el bloqueador de popups.</p>

        <script src="https://checkout.bold.co/library/boldPaymentButton.js"></script>
        <script>
            (function () {
                const config = @json($config);
                const button = document.getElementById('bold-custom-button');

                console.log('[Bold] config', config);

                if (! window.BoldCheckout) {
                    console.error('[Bold] BoldCheckout no disponible');
                    return;
                }

                const checkout = new window.BoldCheckout(config);

                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    try {
                        checkout.open();
                    } catch (error) {
                        console.error('[Bold] open error', error);
                        alert('No pudimos abrir Bold: ' + (error?.message || 'Error desconocido'));
                    }
                });
            })();
        </script>
    </div>
</x-shop::layouts>
