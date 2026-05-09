<?php

namespace App\Http\Controllers\Settings;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;

class StoreSettingController extends BaseController
{
    public function __construct()
    {
        $this->middleware('can:settings.store');
    }

    public function index()
    {
        $settings = [
            'store.name'        => Setting::get('store.name', ''),
            'store.address'     => Setting::get('store.address', ''),
            'store.phone'       => Setting::get('store.phone', ''),
            'store.email'       => Setting::get('store.email', ''),
            'store.tax_number'  => Setting::get('store.tax_number', ''),
            'store.footer_note' => Setting::get('store.footer_note', 'Terima kasih atas kunjungan Anda'),
            'store.logo'        => Setting::get('store.logo', ''),
        ];

        return view('settings.store', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'store_name'        => ['required', 'string', 'max:255'],
            'store_address'     => ['nullable', 'string', 'max:500'],
            'store_phone'       => ['nullable', 'string', 'max:50'],
            'store_email'       => ['nullable', 'email', 'max:255'],
            'store_tax_number'  => ['nullable', 'string', 'max:50'],
            'store_footer_note' => ['nullable', 'string', 'max:255'],
            'store_logo'        => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ]);

        if ($request->hasFile('store_logo')) {
            $oldLogo = Setting::get('store.logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            $path = $request->file('store_logo')->store('settings', 'public');
            Setting::set('store.logo', $path);
        }

        Setting::setMany([
            'store.name'        => $data['store_name'],
            'store.address'     => $data['store_address'] ?? '',
            'store.phone'       => $data['store_phone'] ?? '',
            'store.email'       => $data['store_email'] ?? '',
            'store.tax_number'  => $data['store_tax_number'] ?? '',
            'store.footer_note' => $data['store_footer_note'] ?? '',
        ]);

        return back()->with('success', 'Pengaturan toko berhasil disimpan.');
    }
}
