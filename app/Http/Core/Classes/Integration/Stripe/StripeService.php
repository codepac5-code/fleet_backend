<?php
namespace App\Http\Core\Classes\Integration\Stripe;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Stripe\EphemeralKey;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Customer;
use Laravel\Cashier\Cashier;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a PaymentIntent and reserve the amount
     */

    public function createPaymentIntent($price , $currency = 'aed', $payment_method_types =['card'] )
    {
        $user = Auth::user();

        if( $user->stripe_customer_id == null) {
            $customer = Customer::create([
                    'phone' => $user->phoneNumber,
                    'name'  => $user->firstName,
            ]);

            $user->stripe_customer_id = $customer->id;
            $user->save();
        }


        $intent = PaymentIntent::create(params: [
            'amount' => $this->convertToCents($price ),
            'currency' => $currency,
            'payment_method_types' => ['card'],
            'capture_method' => 'manual',
            'customer' => $user->stripe_customer_id,
        //  'automatic_payment_methods' => ['enabled' => true],
            'setup_future_usage' => 'off_session',
        ]);


        $ephemeralKey = EphemeralKey::create(
            ['customer' => $user->stripe_customer_id] ,
            ['stripe_version' =>'2023-10-16']
        );

        // $booking->stripe_payment_intent_id = $intent->id;
        // $booking->paymentStatus = 'pending';
        // $booking->save()

        //$intent ->ephemeralKey = $ephemeralKey->secret;
         $intent['ephemeralKey']= $ephemeralKey;//y//->secret;
        return $intent;
    }



    /**
     * Check the payment status
     */

    public function getPaymentStatus($booking)
    {
        if (!$booking->stripe_payment_intent_id) {
            return 'no_payment_intent';
        }

        $intent = PaymentIntent::retrieve($booking->stripe_payment_intent_id);
        $booking->paymentStatus = $intent->status;
        $booking->save();

        return $intent->status;
    }

    /**
     * Capture the payment after the order ends
     */

    public function capturePayment($booking)
    {
        $intent = PaymentIntent::retrieve($booking->stripe_payment_intent_id);

        if ($intent->status === 'requires_capture') {
            $capturedIntent = $intent->capture();
            $booking->paymentStatus = $capturedIntent->status === 'succeeded' ? 'paid' : 'failed';
            $booking->PaymentDatetime = now();
            $booking->save();

            return $capturedIntent->status;
        }

        return $intent->status;
    }


    /**
     * Update the payment amount before capture
     */

    public function updatePaymentAmount($booking, $newAmount)
    {
        $intent = PaymentIntent::update(
            $booking->stripe_payment_intent_id,
            [
                'amount' => $this->convertToCents($newAmount)
            ]
        );

        $booking->totalAmount = $newAmount;
        $booking->save();

        return $intent;
    }

    /**
     * Cancel the payment if the trip is canceled or the payment fails
     */

    public function cancelPayment($booking)
    {
        $intent = PaymentIntent::retrieve($booking->stripe_payment_intent_id);

        if (in_array($intent->status, ['requires_capture', 'requires_payment_method'])) {
            $intent->cancel();
            $booking->paymentStatus = 'canceled';
            $booking->save();

            return 'canceled';
        }

        return $intent->status;
    }


    private function convertToCents($amount)
    {
        return (int) round($amount * 100);
    }

    public function getPaymentStatusMessage($booking)
    {

        $paymentStatus = $this->getPaymentStatus($booking);
        switch ($paymentStatus) {
            case 'pending':
                return 'تم حجز المبلغ على بطاقتك، سيتم السحب بعد انتهاء الرحلة.';
            case 'paid':
                return 'تم سحب المبلغ بنجاح، شكراً لاستخدامك خدمتنا.';
            case 'failed':
                return 'فشل الدفع، يرجى تحديث وسيلة الدفع الخاصة بك.';
            case 'canceled':
                return 'تم إلغاء الدفع، لن يتم خصم أي مبلغ.';
            default:
                return 'لم يتم تحديد حالة الدفع بعد.';
        }
    }
}
