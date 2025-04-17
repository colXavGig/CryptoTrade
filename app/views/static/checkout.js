const stripe = Stripe("pk_test_51REaJN2eoqplFxF1UMuVHN23nLMNgUcVm6IvknoZQGPUQ28Ph9JfS694qkAceDg4gH5pLcHc2PulZccE5T1iWlw500XOyz9tk3")

initialize();

async function initialize() {
    const fetchClientSecret = async () => {
        const response = await fetch("/api/user/checkout", {
            method: "POST",
        });
        const { clientSecret } = await response.json();
        return clientSecret;
    };

    const checkout = await stripe.initEmbeddedCheckout({
        fetchClientSecret,
    });

    // Mount Checkout
    checkout.mount('#checkout');
}

