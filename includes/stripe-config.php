<?php
/**
 * Stripe Configuration
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load local environment values from .env if present
$env_path = __DIR__ . '/../.env';
if (file_exists($env_path)) {
    foreach (file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $pair = explode('=', $line, 2);
        if (count($pair) !== 2) {
            continue;
        }

        [$key, $value] = $pair;
        $key = trim($key);
        $value = trim($value);

        if ($key === '') {
            continue;
        }

        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

$stripe_config = [
    'publishable_key' => getenv('STRIPE_PUBLISHABLE_KEY') ?: '',
    'secret_key' => getenv('STRIPE_SECRET_KEY') ?: '',
];

// Initialize Stripe
if (!empty($stripe_config['secret_key'])) {
    \Stripe\Stripe::setApiKey($stripe_config['secret_key']);
} else {
    error_log('Stripe configuration error: STRIPE_SECRET_KEY is not set.');
}

/**
 * Create payment intent for order
 */
if (!function_exists('create_payment_intent')) {
    function create_payment_intent($amount, $order_id, $customer_email) {
        try {
            $intent = \Stripe\PaymentIntent::create([
                'amount' => intval($amount * 100), // Convert to cents
                'currency' => 'pkr', // Pakistani Rupees
                'payment_method_types' => ['card'],
                'metadata' => [
                    'order_id' => $order_id,
                    'customer_email' => $customer_email
                ],
                'statement_descriptor_suffix' => 'Diffindo Cakes'
            ]);
            
            return [
                'success' => true,
                'client_secret' => $intent->client_secret,
                'payment_intent_id' => $intent->id
            ];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log('Stripe Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

/**
 * Confirm payment intent
 */
if (!function_exists('confirm_payment_intent')) {
    function confirm_payment_intent($payment_intent_id) {
        try {
            $intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
            
            $charge_id = $intent->charges->data[0]->id ?? $intent->latest_charge ?? null;
            
            return [
                'success' => $intent->status === 'succeeded',
                'status' => $intent->status,
                'charge_id' => $charge_id
            ];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log('Stripe Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

/**
 * Refund payment
 *
 * Supports refund by charge ID, with fallback to payment intent charge lookup.
 */
if (!function_exists('refund_payment')) {
    function refund_payment($charge_id = null, $amount = null, $payment_intent_id = null) {
        try {
            if (empty($charge_id) && !empty($payment_intent_id)) {
                $intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
                $charge_id = $intent->charges->data[0]->id ?? $intent->latest_charge ?? null;
            }

            if (empty($charge_id)) {
                return [
                    'success' => false,
                    'error' => 'Unable to determine charge ID for refund.'
                ];
            }

            $refund_params = [
                'charge' => $charge_id
            ];
            
            // If partial refund, include amount in cents
            if ($amount !== null) {
                $refund_params['amount'] = intval($amount * 100);
            }
            
            $refund = \Stripe\Refund::create($refund_params);
            
            return [
                'success' => $refund->status === 'succeeded',
                'refund_id' => $refund->id,
                'status' => $refund->status,
                'amount_refunded' => $refund->amount / 100
            ];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log('Stripe Refund Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

return $stripe_config;
?>
