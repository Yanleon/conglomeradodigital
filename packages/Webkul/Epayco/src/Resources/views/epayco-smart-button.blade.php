@if (
    request()->routeIs('shop.checkout.onepage.index')
    && (bool) core()->getConfigData('sales.payment_methods.epayco.active')
)
    @php
        $public_key  = core()->getConfigData('sales.payment_methods.epayco.public_key');
        $testing_mode = core()->getConfigData('sales.payment_methods.epayco.testing_mode');
    @endphp

    @pushOnce('scripts')

        {{-- ✅ Script correcto de ePayco --}}
        <script src="https://checkout.epayco.co/checkout.js"></script>

        {{-- Template del botón --}}
        <script
            type="text/x-template"
            id="v-epayco-button-template"
        >
            <div class="w-full flex justify-end epayco-button-container">
                <x-epayco::button-to-send
                    type="button"
                    class="primary-button w-max rounded-2xl bg-navyBlue px-11 py-3 max-md:mb-4 max-md:w-full max-md:max-w-full max-md:rounded-lg max-sm:py-1.5"
                    :title="trans('shop::app.checkout.onepage.summary.place-order')"
                    ::disabled="isPlacingOrder"
                    ::loading="isPlacingOrder"
                    @click="createOrder"
                />
            </div>
        </script>

        {{-- ✅ IMPORTANTE: SIN type="module" --}}
        <script>
            document.addEventListener("DOMContentLoaded", function () {

                app.component('v-epayco-button', {
                    template: '#v-epayco-button-template',

                    data() {
                        return {
                            isPlacingOrder: false
                        }
                    },

                    methods: {
                        register() {
                            console.log('loading Epayco Smart Button');
                        },

                        createOrder() {
                            this.isPlacingOrder = true;

                            return this.$axios.get("{{ route('epayco.standard.set-order') }}")
                                .then(response => {

                                    // 🔍 Validación extra (debug)
                                    if (typeof ePayco === "undefined") {
                                        console.error("❌ ePayco no está cargado");
                                        return;
                                    }

                                    let handler = ePayco.checkout.configure({
                                        key: '{{ $public_key }}',
                                        test: {{ $testing_mode == 1 ? 'true' : 'false' }},
                                    });

                                    handler.open(response.data);
                                })
                                .catch(error => {
                                    console.log(error);
                                    this.isPlacingOrder = false;
                                });
                        },
                    }
                });

            });
        </script>

    @endPushOnce
@endif