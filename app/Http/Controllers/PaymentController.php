<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\Registration;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mollie\Laravel\Facades\Mollie;

class EditionPaymentController extends Controller
{
    public function initiatePayment($editionId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $edition = Edition::findOrFail($editionId);

        $registration = Registration::where('user_id', Auth::id())
            ->where('edition_id', $editionId)
            ->where('status', 'approved')
            ->where('is_paid', false)
            ->first();

        if (!$registration) {
            return redirect()->route('editions.register', $editionId)
                ->with('error', 'No approved registration found or payment already completed.');
        }

        $daysCount = count($registration->attendance_days);
        $pricePerDay = 15.00;
        $campingFee = $registration->staying_for_camping ? 10.00 : 0.00;
        $totalAmount = ($daysCount * $pricePerDay) + $campingFee;

        $payment = new Payment();
        $payment->user_id = Auth::id();
        $payment->registration_detail_id = $registration->id;
        $payment->amount = $totalAmount;
        $payment->currency = 'EUR';
        $payment->status = 'pending';
        $payment->save();

        $molliePayment = Mollie::api()->payments()->create([
            'amount' => [
                'currency' => 'EUR',
                'value' => number_format($totalAmount, 2, '.', ''),
            ],
            'description' => $edition->name . ' Registration - ' . Auth::user()->name,
            'redirectUrl' => route('edition.payment.verification', [
                'editionId' => $editionId,
                'payment_id' => $payment->id
            ]),
            'webhookUrl' => route('edition.payment.webhook'),
            'method' => 'ideal',
            'metadata' => [
                'payment_id' => $payment->id,
                'user_id' => Auth::id(),
                'registration_id' => $registration->id,
                'edition_id' => $editionId,
            ],
        ]);

        $payment->payment_id = $molliePayment->id;
        $payment->save();

        return redirect($molliePayment->getCheckoutUrl(), 303);
    }

    public function verifyPayment(Request $request, $editionId, $payment_id)
    {
        $payment = Payment::findOrFail($payment_id);
        $edition = Edition::findOrFail($editionId);
        $molliePayment = Mollie::api()->payments()->get($payment->payment_id);

        if ($molliePayment->isPaid()) {

            $payment->status = 'paid';
            $payment->save();

            $registration = Registration::find($payment->registration_detail_id);
            $registration->is_paid = true;
            $registration->save();

            $user = User::find($payment->user_id);
            $user->role = 'participant';
            $user->save();

            $edition->participants()->syncWithoutDetaching([$user->id]);

            return redirect()->route('editions.register', $editionId)
                ->with('message', 'Payment successful! You are now registered as a participant for ' . $edition->name);
        } else {
            return redirect()->route('editions.register', $editionId)
                ->with('error', 'Payment was not completed. Please try again or contact support.');
        }
    }

    public function handleWebhook(Request $request)
    {
        $molliePaymentId = $request->input('id');
        $molliePayment = Mollie::api()->payments()->get($molliePaymentId);

        $payment = Payment::where('payment_id', $molliePaymentId)->first();

        if (!$payment) {
            return response('Payment not found', 404);
        }

        if ($molliePayment->isPaid()) {
            $payment->status = 'paid';
            $payment->save();

            $registration = Registration::find($payment->registration_detail_id);
            if ($registration) {
                $registration->is_paid = true;
                $registration->save();

                $user = User::find($payment->user_id);
                if ($user) {
                    $user->role = 'participant';
                    $user->save();

                    $edition = Edition::find($registration->edition_id);
                    if ($edition) {
                        $edition->participants()->syncWithoutDetaching([$user->id]);
                    }
                }
            }
        } elseif ($molliePayment->isCanceled() || $molliePayment->isExpired() || $molliePayment->isFailed()) {
            $payment->status = 'failed';
            $payment->save();
        }

        return response('Webhook processed', 200);
    }
}
