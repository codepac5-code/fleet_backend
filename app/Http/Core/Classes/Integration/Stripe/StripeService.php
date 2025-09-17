<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\PaymentIntent;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a PaymentIntent and reserve the amount
     */

    public function createPaymentIntent($price , $currency = 'usd', $payment_method_types =['card'] )
    {
        $intent = PaymentIntent::create([
            'amount' => $price * 100, 
            'currency' => 'usd',
            'payment_method_types' => ['card'],
            'capture_method' => 'manual', 
        ]);

        // $booking->stripe_payment_intent_id = $intent->id;
        // $booking->paymentStatus = 'pending';
        // $booking->save();

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
                'amount' => $newAmount * 100
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


    
    public function getPaymentMessage($booking)
    {
        switch ($booking->paymentStatus) {
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
