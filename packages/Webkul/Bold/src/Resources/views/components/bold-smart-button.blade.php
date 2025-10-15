<div id="bold-button-container"></div>

<script src="https://checkout.bold.co/sdk.js"></script>

<script>
  const total = "{{ $total }}";
  const publicKey = "{{ $publicKey }}";

  BoldCheckout.configure({
    publicKey: publicKey,
    currency: 'COP',
    amount: total,
    reference: 'ORD-' + Date.now(),
    onSuccess: function(response) {
      window.location.href = '/checkout/success';
    },
    onError: function(error) {
      alert('Error al procesar el pago');
      console.error(error);
    }
  });

  BoldCheckout.renderButton('#bold-button-container');
</script>
