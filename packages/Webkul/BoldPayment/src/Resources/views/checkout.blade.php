<x-shop::layouts :has-feature="false">
    <x-slot:title>
        Finaliza tu pago con Bold
    </x-slot>

    <div class="max-w-3xl mx-auto px-6 py-10 text-center">
        <script src="https://checkout.bold.co/library/boldPaymentButton.js"></script>

        <script>
            (function () {
                const cfg = @json($config);
                const script = document.createElement('script');

                script.setAttribute('data-bold-button', 'dark-L');
                script.dataset.apiKey = cfg.apiKey;
                script.dataset.orderId = cfg.orderId;
                script.dataset.currency = cfg.currency;
                script.dataset.amount = cfg.amount;
                script.dataset.integritySignature = cfg.integritySignature;
                script.dataset.description = cfg.description;
                script.dataset.redirectionUrl = cfg.redirectionUrl;
                script.dataset.renderMode = cfg.renderMode || 'embedded';

                if (cfg.originUrl) script.dataset.originUrl = cfg.originUrl;
                if (cfg.customerData) script.setAttribute('data-customer-data', cfg.customerData);
                if (cfg.billingAddress) script.setAttribute('data-billing-address', cfg.billingAddress);
                if (cfg.extraData1) script.dataset.extraData1 = cfg.extraData1;
                if (cfg.extraData2) script.dataset.extraData2 = cfg.extraData2;
                if (cfg.tax) script.dataset.tax = cfg.tax;
                if (cfg.expirationDate) script.dataset.expirationDate = cfg.expirationDate;

                document.body.appendChild(script);

                console.debug('[Bold] script appended', script.dataset);
            })();
        </script>
    </div>
</x-shop::layouts>
