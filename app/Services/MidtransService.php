<?php

namespace App\Services;

use App\Models\Setting;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey    = Setting::get('midtrans.server_key', config('midtrans.server_key'));
        Config::$isProduction = Setting::get('midtrans.is_production', '0') === '1';
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    public function isEnabled(): bool
    {
        return Setting::get('midtrans.enabled', '0') === '1'
            && !empty(Setting::get('midtrans.server_key'));
    }

    public function getClientKey(): string
    {
        return Setting::get('midtrans.client_key', config('midtrans.client_key')) ?? '';
    }

    public function isProduction(): bool
    {
        return Setting::get('midtrans.is_production', '0') === '1';
    }

    public function getSnapToken(array $params): string
    {
        return Snap::getSnapToken($params);
    }

    public function buildSnapParams(string $orderId, float $amount, array $customerDetails, array $items): array
    {
        return [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => (int) round($amount),
            ],
            'customer_details' => $customerDetails,
            'item_details'     => $items,
        ];
    }

    public function getNotification(): Notification
    {
        return new Notification();
    }

    public function getSnapJsUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }
}
