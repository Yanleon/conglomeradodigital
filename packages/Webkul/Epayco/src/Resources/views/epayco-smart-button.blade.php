@if (
    request()->routeIs('shop.checkout.onepage.index')
    && (bool) core()->getConfigData('sales.payment_methods.epayco.active')
)
    @php
    $public_key  = core()->getConfigData('sales.payment_methods.epayco.public_key');
    $testing_mode = core()->getConfigData('sales.payment_methods.epayco.testing_mode');
    @endphp

    @pushOnce('scripts')

<script type="text/javascript" src="https://epayco.co/checkout.js"></script>

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

                    createOrder() {
                        this.isPlacingOrder = true;
                        return this.$axios.get("{{ route('epayco.standard.set-order') }}")
                        .then(response => {
                            let handler = ePayco.checkout.configure({
                                key: '{{ $public_key }}',
                                test: '{{ $testing_mode == 1 ? 'true' : 'false' }}',
                            });
                            handler.open(response.data);
                        })
                        .catch(error => {
                            console.log(error);
                        });
                    },
                }
            });
        </script>
    @endPushOnce
@endif
