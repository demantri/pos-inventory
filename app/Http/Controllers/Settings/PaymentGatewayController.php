<?php

namespace App\Http\Controllers\Settings;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class PaymentGatewayController extends BaseController
{
    public function __construct()
    {
        $this->middleware('can:settings.payment_gateway');
    }

    public function index()
    {
        $settings = [
            'midtrans.enabled'       => Setting::get('midtrans.enabled', '0'),
            'midtrans.server_key'    => Setting::get('midtrans.server_key', ''),
            'midtrans.client_key'    => Setting::get('midtrans.client_key', ''),
            'midtrans.is_production' => Setting::get('midtrans.is_production', '0'),
        ];

        return view('settings.payment-gateway', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'midtrans_enabled'       => ['nullable', 'boolean'],
            'midtrans_server_key'    => ['nullable', 'string', 'max:255'],
            'midtrans_client_key'    => ['nullable', 'string', 'max:255'],
            'midtrans_is_production' => ['nullable', 'boolean'],
        ]);

        Setting::setMany([
            'midtrans.enabled'       => $request->boolean('midtrans_enabled') ? '1' : '0',
            'midtrans.server_key'    => $data['midtrans_server_key'] ?? '',
            'midtrans.client_key'    => $data['midtrans_client_key'] ?? '',
            'midtrans.is_production' => $request->boolean('midtrans_is_production') ? '1' : '0',
        ]);

        return back()->with('success', 'Pengaturan payment gateway berhasil disimpan.');
    }
}
