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
            disabled
        >
            {{ $buttonLabel }}
            @if($amount > 0)
                <span class="ml-2">{{ $currency }} {{ number_format($amount, 0, ',', '.') }}</span>
            @endif
        </button>

        <p id="bold-status" class="text-sm text-gray-600 mt-3">Cargando Bold…</p>

        <script>
            (() => {
                const config = @json($config);
                const button = document.getElementById('bold-custom-button');
                const status = document.getElementById('bold-status');
                const boldSrc = 'https://checkout.bold.co/library/boldPaymentButton.js';
                let checkoutInstance = null;
                let readinessTimer = null;

                const setStatus = (text, isError = false) => {
                    status.textContent = text;
                    status.classList.toggle('text-red-600', isError);
                    console.log('[Bold] status:', text);
                };

                const initBoldCheckout = () => {
                    if (document.querySelector(`script[src="${boldSrc}"]`)) {
                        return;
                    }

                    const js = document.createElement('script');

                    js.onload = () => {
                        console.log('[Bold] script loaded');
                        window.dispatchEvent(new Event('boldCheckoutLoaded'));
                    };
                    js.onerror = () => window.dispatchEvent(new Event('boldCheckoutLoadFailed'));

                    js.src = boldSrc;
                    document.head.appendChild(js);

                    readinessTimer = setTimeout(() => {
                        if (! window.BoldCheckout) {
                            setStatus('No se pudo cargar Bold. Revisa tu conexión o intenta de nuevo.', true);
                            button.disabled = true;
                        }
                    }, 5000);
                };

                const setupButton = () => {
                    if (! window.BoldCheckout) {
                        return;
                    }

                    try {
                        console.log('[Bold] init config', config);
                        checkoutInstance = new window.BoldCheckout(config);
                        if (readinessTimer) clearTimeout(readinessTimer);
                        button.disabled = false;
                        setStatus('Listo para pagar');
                    } catch (error) {
                        console.error('BoldCheckout init error', error);
                        setStatus('No pudimos iniciar Bold: ' + (error?.message || 'Error desconocido'), true);
                        button.disabled = true;
                        return;
                    }
                };

                const openCheckout = (event) => {
                    event?.preventDefault();

                    if (! checkoutInstance) {
                        setStatus('Aún no hemos cargado Bold. Reintenta en unos segundos.', true);
                        return;
                    }

                    try {
                        setStatus('Abriendo Bold...');
                        console.log('[Bold] open checkout');
                        checkoutInstance.open();
                        setStatus('Si no ves la ventana, revisa bloqueador de popups.');
                    } catch (error) {
                        console.error('BoldCheckout open error', error);
                        setStatus('No pudimos abrir Bold: ' + (error?.message || 'Error desconocido'), true);
                    }
                };

                button.addEventListener('click', openCheckout);

                window.addEventListener('boldCheckoutLoaded', setupButton, { once: true });

                window.addEventListener('boldCheckoutLoadFailed', () => {
                    button.disabled = true;
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
</x-shop::layouts>
