@if (
    request()->routeIs('shop.checkout.onepage.index')
    && (bool) core()->getConfigData('sales.payment_methods.epayco.active')
)
    @php
    $public_key  = core()->getConfigData('sales.payment_methods.epayco.public_key');
    $testing_mode = core()->getConfigData('sales.payment_methods.epayco.testing_mode');
    @endphp

    @pushOnce('scripts')

        <script type="text/javascript" src="https://checkout.epayco.co/checkout-v2.js"></script>

        <script
            type="text/x-template"
            id="v-epayco-button-template"
        >
            <div class="w-full flex justify-end epayco-button-container">
                <x-epayco::button-to-send
                    type="button"
                    class="primary-button w-max rounded-2xl bg-navyBlue px-11 py-3 max-md:mb-4 max-md:w-full max-md:max-w-full max-md:rounded-lg max-sm:py-1.5"
                    {{-- traduccion de titulo de boton glolbal shopo package, realizar pedido --}}
                    :title="trans('shop::app.checkout.onepage.summary.place-order')"
                     ::disabled="isPlacingOrder"
                     ::loading="isPlacingOrder"
                     @click="createOrder"
                 />
             </div>
        </script>

        <script type="module">
            app.component('v-epayco-button', {
                template: '#v-epayco-button-template',
                data(){
                    return{
                        isPlacingOrder : false
                    }
                },
                mounted() {
                    //console.log(this.isPlacingOrder);
                },

                methods: {
                    register() {
                        console.log('loading Epayco Smart Button');
                    },

                    showError(message) {
                        if (this.$emitter && typeof this.$emitter.emit === 'function') {
                            this.$emitter.emit('add-flash-message', {
                                type: 'error',
                                message: message || 'No fue posible iniciar el pago con ePayco.'
                            });
                        } else {
                            alert(message || 'No fue posible iniciar el pago con ePayco.');
                        }
                    },

                    createOrder() {
                        this.isPlacingOrder = true;

                        return this.$axios.post("{{ route('epayco.standard.set-order') }}")
                            .then(response => {
                                const checkout = ePayco.checkout.configure({
                                    sessionId: response.data.sessionId,
                                    type: "onpage",
                                    test: response.data.test,
                                });

                                checkout.onCreated(() => {
                                    this.isPlacingOrder = false;
                                });

                                checkout.onErrors(errors => {
                                    console.error('Error ePayco:', errors);
                                    this.isPlacingOrder = false;
                                    this.showError('Error al crear la transacción en ePayco.');
                                });

                                checkout.onClosed(() => {
                                    this.isPlacingOrder = false;
                                });

                                checkout.open();
                            })
                            .catch(error => {
                                console.log(error);
                                this.isPlacingOrder = false;
                                this.showError(error?.response?.data?.message || 'No fue posible iniciar el pago con ePayco.');
                            });
                    },
                }
            });
        </script>
    @endPushOnce
@endif
