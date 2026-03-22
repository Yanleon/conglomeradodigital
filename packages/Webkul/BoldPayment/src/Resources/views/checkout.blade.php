<x-shop::layouts
    :has-header="true"
    :has-feature="false"
    :has-footer="false"
>
    <x-slot:title>
        Finaliza tu pago con Bold
    </x-slot>

    @push('styles')
        <style>
            body { background: #f4f6f9; }

            .bold-landing {
                min-height: calc(100vh - 140px);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 56px 16px;
            }

            .bold-card {
                max-width: 560px;
                width: 100%;
                background: linear-gradient(180deg, #ffffff 0%, #fbfbfd 100%);
                border: 1px solid #e6e8f0;
                border-radius: 18px;
                padding: 36px 32px;
                box-shadow: 0 20px 55px rgba(17, 24, 39, 0.14);
                text-align: center;
                transition: transform 0.25s ease, box-shadow 0.25s ease;
            }

            .bold-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 26px 65px rgba(17, 24, 39, 0.18);
            }

            .bold-card h2 {
                font-size: 26px;
                font-weight: 800;
                margin-bottom: 12px;
                color: #0f172a;
                letter-spacing: -0.3px;
            }

            .bold-card p {
                color: #5b6475;
                margin-bottom: 26px;
                font-size: 15px;
            }

            .bold-badge {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 8px 12px;
                border-radius: 999px;
                background: #0f172a;
                color: #e2e8f0;
                font-size: 13px;
                font-weight: 600;
                margin-bottom: 14px;
                box-shadow: 0 6px 18px rgba(15, 23, 42, 0.18);
            }

            .bold-button-slot {
                display: flex;
                justify-content: center;
            }

            .bold-footer-note {
                margin-top: 18px;
                font-size: 13px;
                color: #94a3b8;
            }
        </style>
    @endpush

    <section class="bold-landing">
        <div class="bold-card">
            <div class="bold-badge">Pago seguro con Bold</div>
            <h2>Finaliza tu pago con Bold</h2>
            <p>Confirma el pago sin salir de la tienda.</p>

            <div class="bold-button-slot">
                <script
                    src="https://checkout.bold.co/library/boldPaymentButton.js"
                    data-bold-button="{{ $buttonStyle }}"
                    data-api-key="{{ $apiKey }}"
                    data-merchant-id="{{ $merchantId }}"
                    data-order-id="{{ $orderId }}"
                    data-currency="{{ $currency }}"
                    data-description="{{ $description }}"
                    data-redirection-url="{{ $redirectUrl }}"
                    data-render-mode="{{ $renderMode ?? 'embedded' }}"
                    data-amount="{{ $amount > 0 ? $amount : '' }}"
                    data-integrity-signature="{{ $amount > 0 ? $signature : '' }}"
                    data-environment="{{ $environment }}"
                ></script>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const scriptEl = document.querySelector('script[data-bold-button]');
                    if (!scriptEl) {
                        return;
                    }

                    const dataset = scriptEl.dataset;

                    if (!dataset.apiKey) {
                        console.error('Bold: falta API Key en la configuración.');
                        return;
                    }

                    const buildConfig = () => ({
                        apiKey: dataset.apiKey,
                        merchantId: dataset.merchantId,
                        orderId: dataset.orderId,
                        currency: dataset.currency,
                        description: dataset.description,
                        redirectionUrl: dataset.redirectionUrl,
                        renderMode: dataset.renderMode || 'embedded',
                        amount: parseInt(dataset.amount || '0', 10) || 0,
                        integritySignature: dataset.integritySignature,
                        environment: dataset.environment || 'production',
                    });

                    const attachHandler = () => {
                        const boldButton = scriptEl.nextElementSibling?.shadowRoot?.querySelector('#boldPaymentButton')
                            || scriptEl.nextElementSibling;

                        if (!boldButton) {
                            return;
                        }

                        // Evita duplicar listeners
                        boldButton.removeEventListener('click', boldButton._boldClickHandler || (() => {}));

                        boldButton._boldClickHandler = (event) => {
                            event.preventDefault();
                            try {
                                const checkout = new BoldCheckout(buildConfig());
                                checkout.open();
                            } catch (error) {
                                console.error('Bold: error al abrir el checkout', error);
                            }
                        };

                        boldButton.addEventListener('click', boldButton._boldClickHandler);
                    };

                    // Permite que la librería renderice el botón antes de enlazarlo
                    setTimeout(attachHandler, 300);
                });
            </script>

            <div class="bold-footer-note">Serás redirigido al finalizar el pago.</div>
        </div>
    </section>
</x-shop::layouts>
