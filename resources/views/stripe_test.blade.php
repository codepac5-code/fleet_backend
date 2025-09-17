<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-md">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Pay for Booking #{{ $booking->id }}</h2>
        <p class="text-lg text-gray-600 mb-6">Amount: <span class="font-semibold">${{ $booking->totalAmount }}</span></p>

        <form id="payment-form" class="space-y-4">
            <!-- Card Element -->
            <div id="card-element" class="p-3 border border-gray-300 rounded-md"></div>

            <!-- Postal Code -->
            <input type="text" id="postal-code" placeholder="Postal Code"
                class="w-full p-3 border border-gray-300 rounded-md" value="12345" />

            <!-- Phone -->
            <input type="text" id="phone" placeholder="Phone Number"
                class="w-full p-3 border border-gray-300 rounded-md" value="5555555555" />

            <button id="submit"
                class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition">
                Pay Now
            </button>
        </form>

        <!-- Status Message -->
        <div id="payment-message" class="mt-4 text-center text-sm text-gray-700"></div>
    </div>

    <script>
        const stripe = Stripe("{{ config('services.stripe.public') }}");
        const elements = stripe.elements();
        const cardElement = elements.create("card", {hidePostalCode: true});
        cardElement.mount("#card-element");

        const form = document.getElementById("payment-form");
        const messageEl = document.getElementById("payment-message");

        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            const postalCode = document.getElementById("postal-code").value;
            const phone = document.getElementById("phone").value;

            const { paymentMethod, error } = await stripe.createPaymentMethod({
                type: "card",
                card: cardElement,
                billing_details: {
                    address: { postal_code: postalCode },
                    phone: phone
                }
            });

            if (error) {
                messageEl.textContent = error.message;
                messageEl.classList.add("text-red-600");
            } else {
                const response = await fetch("{{ route('payments.process') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        payment_method: paymentMethod.id,
                        booking_id: "{{ $booking->id }}"
                    }),
                });

                const result = await response.json();

                if (result.error) {
                    messageEl.textContent = result.error;
                    messageEl.classList.add("text-red-600");
                } else {
                    if (result.status === "succeeded") {
                        messageEl.textContent = "Payment successful!";
                        messageEl.classList.add("text-green-600");
                    } else {
                        messageEl.textContent = "Payment status: " + result.status;
                        messageEl.classList.add("text-gray-700");
                    }
                }
            }
        });
    </script>
</body>
</html>
