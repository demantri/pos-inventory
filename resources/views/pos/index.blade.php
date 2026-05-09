@extends('layouts.pos')
@section('title', 'Kasir — POS')

@php
    $midtransEnabled = \App\Models\Setting::get('midtrans.enabled', '0') === '1';
    $midtransClientKey = \App\Models\Setting::get('midtrans.client_key', '');
    $midtransIsProduction = \App\Models\Setting::get('midtrans.is_production', '0') === '1';
    $midtransSnapJs = $midtransIsProduction
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
@endphp

@section('content')
    <div style="display:flex; height:100%; overflow:hidden;">

        {{-- ── Panel Kiri: Produk ── --}}
        <div style="display:flex; flex-direction:column; flex:1; min-width:0; overflow:hidden;">

            {{-- Search + Filter --}}
            <div
                style="flex-shrink:0; background:#fff; border-bottom:1px solid #e5e7eb;
                    padding:12px 16px; display:flex; flex-direction:column; gap:8px;">
                <div style="position:relative;">
                    <svg style="position:absolute;left:12px;top:50%;transform:translateY(-50%);
                             width:16px;height:16px;color:#9ca3af;"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="search-product" placeholder="Cari produk atau scan barcode..." autofocus
                        style="width:100%;padding:10px 16px 10px 40px;border:1px solid #d1d5db;
                              border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                </div>
                <div style="display:flex;gap:8px;overflow-x:auto;" id="category-filter">
                    <button onclick="filterCategory(null, this)"
                        style="flex-shrink:0;padding:6px 14px;border-radius:8px;font-size:12px;
                               font-weight:600;background:#2563eb;color:#fff;border:none;cursor:pointer;">
                        Semua
                    </button>
                    @php $categories = collect($products)->pluck('category')->unique()->sort()->values(); @endphp
                    @foreach ($categories as $cat)
                        <button onclick="filterCategory('{{ $cat }}', this)"
                            style="flex-shrink:0;padding:6px 14px;border-radius:8px;font-size:12px;
                               font-weight:500;background:#f3f4f6;color:#4b5563;border:none;cursor:pointer;">
                            {{ $cat }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Grid Produk --}}
            <div style="flex:1;overflow-y:auto;padding:16px;">
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;"
                    id="product-grid"></div>
                <p style="display:none;text-align:center;color:#9ca3af;font-size:14px;padding:32px 0;" id="no-product-msg">
                    Produk tidak ditemukan</p>
            </div>
        </div>

        {{-- ── Panel Kanan: Keranjang ── --}}
        <div
            style="width:340px;flex-shrink:0;background:#fff;border-left:1px solid #e5e7eb;
                display:flex;flex-direction:column;overflow:hidden;">

            {{-- Header --}}
            <div
                style="flex-shrink:0;padding:12px 16px;border-bottom:1px solid #e5e7eb;
                    display:flex;align-items:center;justify-content:space-between;">
                <span style="font-weight:600;font-size:15px;color:#111827;">Keranjang</span>
                <button onclick="clearCart()"
                    style="font-size:12px;color:#ef4444;background:none;border:none;cursor:pointer;">
                    Kosongkan
                </button>
            </div>

            {{-- Customer --}}
            <div style="flex-shrink:0;padding:10px 16px;border-bottom:1px solid #f3f4f6;">
                <select id="customer-select"
                    style="width:100%;padding:8px 12px;border:1px solid #d1d5db;
                           border-radius:8px;font-size:13px;outline:none;">
                    <option value="">— Pelanggan Umum —</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c['id'] }}">
                            {{ $c['name'] }}{{ $c['phone'] ? ' (' . $c['phone'] . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Item List --}}
            <div style="flex:1;overflow-y:auto;min-height:0;" id="cart-items">
                <div style="display:flex;flex-direction:column;align-items:center;
                        justify-content:center;height:100%;color:#d1d5db;padding:48px 0;"
                    id="cart-empty-state">
                    <svg style="width:48px;height:48px;margin-bottom:8px;" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293
                                                                                     c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4
                                                                                     zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p style="font-size:13px;">Keranjang kosong</p>
                </div>
            </div>

            {{-- Checkout --}}
            <div
                style="flex-shrink:0;border-top:2px solid #e5e7eb;padding:12px 16px;
                    background:#fff;display:flex;flex-direction:column;gap:8px;">

                <div style="display:flex;justify-content:space-between;font-size:13px;color:#6b7280;">
                    <span>Subtotal</span>
                    <span id="summary-subtotal" style="font-weight:500;">Rp 0</span>
                </div>

                <div
                    style="display:flex;align-items:center;justify-content:space-between;
                        font-size:13px;color:#6b7280;">
                    <span>Diskon (Rp)</span>
                    <input type="number" id="discount-input" min="0" value="0" oninput="recalcSummary()"
                        style="width:100px;padding:4px 8px;border:1px solid #e5e7eb;
                              border-radius:6px;font-size:12px;text-align:right;outline:none;">
                </div>

                <div
                    style="display:flex;align-items:center;justify-content:space-between;
                        font-size:13px;color:#6b7280;">
                    <span>Pajak (%)</span>
                    <input type="number" id="tax-input" min="0" max="100" value="0"
                        oninput="recalcSummary()"
                        style="width:100px;padding:4px 8px;border:1px solid #e5e7eb;
                              border-radius:6px;font-size:12px;text-align:right;outline:none;">
                </div>

                <div
                    style="display:flex;justify-content:space-between;align-items:center;
                        padding-top:8px;border-top:2px solid #f3f4f6;">
                    <span style="font-weight:700;font-size:15px;color:#111827;">Total</span>
                    <span style="font-weight:700;font-size:20px;color:#111827;" id="summary-total">Rp 0</span>
                </div>

                <select id="payment-method"
                    style="width:100%;padding:8px 12px;border:1px solid #d1d5db;
                           border-radius:8px;font-size:13px;outline:none;">
                    <option value="cash">Tunai</option>
                    @if($midtransEnabled && $midtransClientKey)
                    <option value="qris">QRIS (Midtrans)</option>
                    <option value="transfer">Transfer Bank (Midtrans)</option>
                    <option value="debit_card">Kartu Debit (Midtrans)</option>
                    <option value="credit_card">Kartu Kredit (Midtrans)</option>
                    @else
                    <option value="qris">QRIS</option>
                    <option value="transfer">Transfer</option>
                    <option value="debit_card">Kartu Debit</option>
                    <option value="credit_card">Kartu Kredit</option>
                    @endif
                </select>

                @if($midtransEnabled && $midtransClientKey)
                <div id="midtrans-info"
                     style="display:none;padding:8px 12px;background:#eff6ff;border:1px solid #bfdbfe;
                            border-radius:8px;font-size:12px;color:#1e40af;">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Akan membuka halaman pembayaran Midtrans
                    </div>
                </div>
                @endif

                <div id="cash-input-wrap">
                    <p style="font-size:12px;color:#6b7280;margin-bottom:4px;">Uang Diterima</p>
                    <div style="position:relative;">
                        <span
                            style="position:absolute;left:10px;top:50%;transform:translateY(-50%);
                                 font-size:13px;color:#9ca3af;">Rp</span>
                        <input type="number" id="paid-input" min="0" value="0" oninput="recalcChange()"
                            style="width:100%;padding:8px 12px 8px 32px;border:1px solid #d1d5db;
                                  border-radius:8px;font-size:13px;outline:none;
                                  box-sizing:border-box;">
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;margin-top:6px;">
                        <span style="color:#6b7280;">Kembalian</span>
                        <span style="font-weight:700;color:#16a34a;font-size:15px;" id="change-display">Rp 0</span>
                    </div>
                </div>

                {{-- Shortcut nominal --}}
                <div id="cash-shortcuts" style="display:flex;gap:5px;">
                    @foreach ([5000, 10000, 20000, 50000, 100000] as $nominal)
                        <button onclick="setPaid({{ $nominal }})"
                            style="flex:1;padding:5px 0;font-size:11px;font-weight:500;
                               border:1px solid #e5e7eb;border-radius:6px;background:#fff;
                               cursor:pointer;color:#374151;"
                            onmouseover="this.style.borderColor='#93c5fd';this.style.color='#2563eb'"
                            onmouseout="this.style.borderColor='#e5e7eb';this.style.color='#374151'">
                            {{ number_format($nominal / 1000, 0) }}rb
                        </button>
                    @endforeach
                    <button onclick="setPaidExact()"
                        style="flex:1;padding:5px 0;font-size:11px;font-weight:600;
                               border:1px solid #bfdbfe;border-radius:6px;background:#eff6ff;
                               cursor:pointer;color:#2563eb;">
                        Pas
                    </button>
                </div>

                <button onclick="submitTransaction()" id="btn-pay"
                    style="width:100%;padding:14px;background:#2563eb;color:#fff;
                           font-weight:700;font-size:16px;border:none;border-radius:12px;
                           cursor:pointer;margin-top:2px;"
                    onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                    Bayar Sekarang
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Resume / Batalkan Pembayaran --}}
    <div id="modal-payment-resume"
        style="display:none;position:fixed;inset:0;z-index:60;
               align-items:center;justify-content:center;background:rgba(0,0,0,0.55);">
        <div style="background:#fff;border-radius:16px;width:100%;max-width:380px;
                    margin:0 16px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
            <div style="background:#fffbeb;padding:24px 24px 20px;border-bottom:1px solid #fde68a;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:48px;height:48px;background:#fef3c7;border-radius:50%;
                                flex-shrink:0;display:flex;align-items:center;justify-content:center;">
                        <svg style="width:24px;height:24px;color:#d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 style="font-size:16px;font-weight:700;color:#92400e;">Pembayaran Belum Selesai</h3>
                        <p style="font-size:13px;color:#b45309;margin-top:2px;">Popup pembayaran ditutup sebelum selesai</p>
                    </div>
                </div>
            </div>
            <div style="padding:20px 24px;">
                <div style="background:#f9fafb;border-radius:10px;padding:14px;margin-bottom:18px;">
                    <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
                        <span style="color:#6b7280;">Metode Bayar</span>
                        <span style="font-weight:600;color:#111827;" id="resume-method-label">—</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:700;">
                        <span style="color:#6b7280;">Total</span>
                        <span style="color:#111827;" id="resume-amount">—</span>
                    </div>
                </div>
                <p style="font-size:12px;color:#9ca3af;margin-bottom:16px;">
                    Transaksi belum dibatalkan. Token pembayaran masih berlaku — Anda bisa melanjutkan atau membatalkan transaksi ini.
                </p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <button onclick="cancelActivePayment()"
                            style="padding:12px;border:1px solid #fecaca;border-radius:10px;
                                   background:#fff;color:#ef4444;font-size:13px;font-weight:600;cursor:pointer;"
                            onmouseover="this.style.background='#fef2f2'"
                            onmouseout="this.style.background='#fff'">
                        Batalkan Transaksi
                    </button>
                    <button onclick="resumePayment()"
                            style="padding:12px;border:none;border-radius:10px;
                                   background:#2563eb;color:#fff;font-size:13px;font-weight:700;cursor:pointer;"
                            onmouseover="this.style.background='#1d4ed8'"
                            onmouseout="this.style.background='#2563eb'">
                        Lanjutkan Bayar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Pending (Transfer / VA) --}}
    <div id="modal-payment-pending"
        style="display:none;position:fixed;inset:0;z-index:60;
               align-items:center;justify-content:center;background:rgba(0,0,0,0.55);">
        <div style="background:#fff;border-radius:16px;width:100%;max-width:400px;
                    margin:0 16px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
            <div style="background:#eff6ff;padding:24px 24px 20px;border-bottom:1px solid #bfdbfe;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:48px;height:48px;background:#dbeafe;border-radius:50%;
                                flex-shrink:0;display:flex;align-items:center;justify-content:center;">
                        <svg style="width:24px;height:24px;color:#2563eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 style="font-size:16px;font-weight:700;color:#1e3a8a;">Menunggu Pembayaran</h3>
                        <p style="font-size:13px;color:#2563eb;margin-top:2px;">Instruksi pembayaran telah dikirim</p>
                    </div>
                </div>
            </div>
            <div style="padding:20px 24px;">
                <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:10px;padding:14px;margin-bottom:16px;">
                    <p style="font-size:12px;color:#0369a1;font-weight:600;margin-bottom:8px;">DETAIL PEMBAYARAN</p>
                    <div id="pending-payment-info" style="font-size:13px;color:#0c4a6e;line-height:1.8;"></div>
                </div>
                <div style="background:#fefce8;border:1px solid #fde047;border-radius:10px;padding:12px;margin-bottom:18px;">
                    <p style="font-size:12px;color:#854d0e;line-height:1.6;">
                        Konfirmasi otomatis akan diterima setelah pembayaran berhasil.
                        Halaman ini tidak perlu ditutup — status akan diperbarui secara otomatis.
                    </p>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <button onclick="cancelActivePayment()"
                            style="padding:12px;border:1px solid #fecaca;border-radius:10px;
                                   background:#fff;color:#ef4444;font-size:13px;font-weight:600;cursor:pointer;"
                            onmouseover="this.style.background='#fef2f2'"
                            onmouseout="this.style.background='#fff'">
                        Batalkan
                    </button>
                    <button onclick="document.getElementById('modal-payment-pending').style.display='none'"
                            style="padding:12px;border:none;border-radius:10px;
                                   background:#2563eb;color:#fff;font-size:13px;font-weight:700;cursor:pointer;"
                            onmouseover="this.style.background='#1d4ed8'"
                            onmouseout="this.style.background='#2563eb'">
                        OK, Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Sukses --}}
    <div id="modal-success"
        style="display:none;position:fixed;inset:0;z-index:50;
            align-items:center;justify-content:center;background:rgba(0,0,0,0.5);">
        <div
            style="background:#fff;border-radius:16px;width:100%;max-width:360px;
                margin:0 16px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
            <div style="background:#f0fdf4;padding:32px 24px;text-align:center;">
                <div
                    style="width:64px;height:64px;background:#dcfce7;border-radius:50%;
                        display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <svg style="width:32px;height:32px;color:#16a34a;" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 style="font-size:20px;font-weight:700;color:#111827;">Transaksi Berhasil!</h3>
                <p style="font-size:13px;color:#6b7280;margin-top:4px;" id="modal-sale-number"></p>
            </div>
            <div style="padding:20px 24px;">
                <div
                    style="display:flex;justify-content:space-between;
                        font-size:14px;margin-bottom:8px;">
                    <span style="color:#6b7280;">Total</span>
                    <span style="font-weight:700;" id="modal-total"></span>
                </div>
                <div
                    style="display:flex;justify-content:space-between;
                        font-size:14px;margin-bottom:8px;">
                    <span style="color:#6b7280;">Dibayar</span>
                    <span id="modal-paid"></span>
                </div>
                <div
                    style="display:flex;justify-content:space-between;font-size:16px;
                        font-weight:700;padding:8px 0;border-top:1px solid #f3f4f6;
                        margin-bottom:16px;">
                    <span style="color:#6b7280;">Kembalian</span>
                    <span style="color:#16a34a;" id="modal-change"></span>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <a id="btn-receipt" href="#" target="_blank"
                        style="padding:10px;border:1px solid #e5e7eb;border-radius:8px;
                          text-align:center;font-size:13px;color:#374151;text-decoration:none;
                          display:block;">
                        Cetak Struk
                    </a>
                    <button onclick="newTransaction()"
                        style="padding:10px;background:#2563eb;color:#fff;border:none;
                               border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;">
                        Transaksi Baru
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // ═════════════════════════════════════════════════════════════
            // TOAST NOTIFICATION
            // ═════════════════════════════════════════════════════════════
            function toast(message, type = 'error', duration = 4000) {
                const container = document.getElementById('toast-container');

                const config = {
                    error: {
                        bg: '#fef2f2',
                        border: '#fecaca',
                        icon: '#ef4444',
                        text: '#991b1b',
                        svg: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>`,
                    },
                    warning: {
                        bg: '#fffbeb',
                        border: '#fde68a',
                        icon: '#f59e0b',
                        text: '#92400e',
                        svg: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667
                           1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464
                           0L3.34 16c-.77 1.333.192 3 1.732 3z"/>`,
                    },
                    success: {
                        bg: '#f0fdf4',
                        border: '#bbf7d0',
                        icon: '#16a34a',
                        text: '#14532d',
                        svg: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>`,
                    },
                    info: {
                        bg: '#eff6ff',
                        border: '#bfdbfe',
                        icon: '#2563eb',
                        text: '#1e3a8a',
                        svg: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>`,
                    },
                };

                const c = config[type] || config.info;
                const id = 'toast-' + Date.now();

                // Support multiline message (pisah dengan \n)
                const lines = message.split('\n').filter(l => l.trim() !== '');
                const msgHtml = lines.length > 1 ?
                    `<ul style="margin:4px 0 0 0;padding-left:16px;list-style:disc;">
               ${lines.map(l => `<li style="margin-bottom:2px;">${l}</li>`).join('')}
                    </ul>` :
                    `<p style="margin:0;font-size:13px;line-height:1.5;">${message}</p>`;

                const el = document.createElement('div');
                el.id = id;
                el.className = 'toast-item';
                el.style.cssText = `
                    min-width:280px;max-width:360px;
                    background:${c.bg};
                    border:1px solid ${c.border};
                    border-radius:12px;
                    padding:12px 14px;
                    box-shadow:0 4px 16px rgba(0,0,0,0.1);
                    display:flex;align-items:flex-start;gap:10px;
                `;
                el.innerHTML = `
                    <svg style="width:20px;height:20px;color:${c.icon};flex-shrink:0;margin-top:1px;"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        ${c.svg}
                    </svg>
                    <div style="flex:1;min-width:0;color:${c.text};">
                        ${msgHtml}
                    </div>
                    <button onclick="dismissToast('${id}')"
                            style="background:none;border:none;cursor:pointer;
                                color:${c.icon};opacity:0.6;flex-shrink:0;
                                padding:0;line-height:1;margin-top:1px;"
                            onmouseover="this.style.opacity='1'"
                            onmouseout="this.style.opacity='0.6'">
                        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                `;

                container.appendChild(el);

                // Auto dismiss
                const timer = setTimeout(() => dismissToast(id), duration);
                el._timer = timer;

                return id;
            }

            function dismissToast(id) {
                const el = document.getElementById(id);
                if (!el) return;
                clearTimeout(el._timer);
                el.classList.add('hiding');
                setTimeout(() => el.remove(), 300);
            }

            function stripDecimal(input) {
                // Hapus semua karakter selain angka
                let val = input.value.replace(/[^0-9]/g, '');
                // Hapus leading zero (misal 007 → 7)
                val = val.replace(/^0+(\d)/, '$1');
                input.value = val;
            }

            // ── Data dari server ──────────────────────────────────────────
            const allProducts = @json($products);
            const csrfToken = '{{ csrf_token() }}';
            const storeUrl = '{{ route('pos.store') }}';
            const snapTokenUrl = '{{ route('payment.snap-token') }}';
            const paymentCompleteBase = '{{ url('payment/complete') }}';
            const paymentCancelBase   = '{{ url('payment/cancel') }}';
            const midtransEnabled = {{ $midtransEnabled && $midtransClientKey ? 'true' : 'false' }};

            // ── State ─────────────────────────────────────────────────────
            let cart = [];
            let activeCategory = null;
            let currentTotal = 0;

            // ═════════════════════════════════════════════════════════════
            // RENDER PRODUK
            // ═════════════════════════════════════════════════════════════
            function renderProducts(list) {
                const grid = document.getElementById('product-grid');
                const noMsg = document.getElementById('no-product-msg');
                grid.innerHTML = '';

                if (list.length === 0) {
                    noMsg.style.display = 'block';
                    return;
                }
                noMsg.style.display = 'none';

                list.forEach(p => {
                    const outOfStock = p.stock <= 0;
                    const card = document.createElement('div');
                    card.style.cssText = `
            background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:12px;
            cursor:${outOfStock ? 'not-allowed' : 'pointer'};
            opacity:${outOfStock ? '0.5' : '1'};
            transition:border-color 0.15s,box-shadow 0.15s;
        `;
                    card.innerHTML = `
            <div style="aspect-ratio:1;background:#f3f4f6;border-radius:8px;margin-bottom:8px;
                        overflow:hidden;display:flex;align-items:center;justify-content:center;">
                ${p.image
                    ? `<img src="${p.image}" alt="${p.name}"
                                                                                                                                            style="width:100%;height:100%;object-fit:cover;">`
                    : `<svg style="width:32px;height:32px;color:#d1d5db;"
                                                                                                                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                                                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                                                                                 d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4
                                                                                                                                                    m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                                                                                                       </svg>`}
            </div>
            <p style="font-size:12px;font-weight:600;color:#111827;line-height:1.4;
                      min-height:32px;display:-webkit-box;-webkit-line-clamp:2;
                      -webkit-box-orient:vertical;overflow:hidden;">${p.name}</p>
            <p style="font-size:11px;color:#9ca3af;margin-top:2px;">${p.code}</p>
            <div style="display:flex;justify-content:space-between;
                        align-items:center;margin-top:6px;">
                <p style="font-size:13px;font-weight:700;color:#2563eb;">
                    Rp ${p.price.toLocaleString('id-ID')}
                </p>
                <span style="font-size:11px;color:${outOfStock ? '#ef4444' : '#9ca3af'};">
                    ${outOfStock ? 'Habis' : 'Stok: ' + p.stock.toLocaleString('id-ID')}
                </span>
            </div>
        `;
                    if (!outOfStock) {
                        card.addEventListener('mouseenter', () => {
                            card.style.borderColor = '#93c5fd';
                            card.style.boxShadow = '0 2px 8px rgba(59,130,246,0.15)';
                        });
                        card.addEventListener('mouseleave', () => {
                            card.style.borderColor = '#e5e7eb';
                            card.style.boxShadow = 'none';
                        });
                        card.addEventListener('click', () => addToCart(p));
                    }
                    grid.appendChild(card);
                });
            }

            // ═════════════════════════════════════════════════════════════
            // FILTER & SEARCH
            // ═════════════════════════════════════════════════════════════
            function filterCategory(cat, btn) {
                activeCategory = cat;
                document.querySelectorAll('#category-filter button').forEach(b => {
                    b.style.background = '#f3f4f6';
                    b.style.color = '#4b5563';
                });
                if (btn) {
                    btn.style.background = '#2563eb';
                    btn.style.color = '#fff';
                }
                applyFilter();
            }

            function applyFilter() {
                const q = document.getElementById('search-product').value.toLowerCase().trim();
                let list = allProducts;
                if (activeCategory) list = list.filter(p => p.category === activeCategory);
                if (q) list = list.filter(p =>
                    p.name.toLowerCase().includes(q) ||
                    p.code.toLowerCase().includes(q) ||
                    (p.barcode && p.barcode.toLowerCase().includes(q))
                );
                renderProducts(list);
            }

            document.getElementById('search-product').addEventListener('input', applyFilter);

            // ═════════════════════════════════════════════════════════════
            // KERANJANG — MANIPULASI DATA
            // ═════════════════════════════════════════════════════════════
            function addToCart(product) {
                const existing = cart.find(i => i.product.id === product.id);
                if (existing) {
                    if (existing.qty >= product.stock) {
                        toast(`Stok ${product.name} hanya ${product.stock} ${product.unit}`, 'warning');
                        return;
                    }
                    existing.qty = parseInt(existing.qty) + 1;
                } else {
                    cart.push({
                        product,
                        qty: 1,
                        price: product.price,
                        discount_percent: 0
                    });
                }
                renderCart();
            }

            function updateQty(productId, delta) {
                const idx = cart.findIndex(i => i.product.id === productId);
                if (idx === -1) return;

                // Pastikan qty saat ini integer
                cart[idx].qty = parseInt(cart[idx].qty) || 1;
                const newQty = cart[idx].qty + delta;

                if (newQty <= 0) {
                    cart.splice(idx, 1);
                    renderCart();
                    return;
                }
                if (newQty > cart[idx].product.stock) {
                    toast(
                        `Stok ${cart[idx].product.name} hanya ${cart[idx].product.stock} ${cart[idx].product.unit}`,
                        'warning'
                    );
                    return;
                }

                cart[idx].qty = newQty;

                const qtyInput = document.getElementById(`qty-input-${productId}`);
                if (qtyInput) qtyInput.value = newQty;

                refreshLineTotal(productId);
                recalcSummary();
            }

            function setQty(productId, val) {
                const idx = cart.findIndex(i => i.product.id === productId);
                if (idx === -1) return;

                // Paksa integer
                const qty = parseInt(val) || 0;

                if (qty <= 0) {
                    cart.splice(idx, 1);
                    renderCart();
                    return;
                }
                if (qty > cart[idx].product.stock) {
                    toast(
                        `Stok ${cart[idx].product.name} hanya ${cart[idx].product.stock} ${cart[idx].product.unit}`,
                        'warning'
                    );
                    const qtyInput = document.getElementById(`qty-input-${productId}`);
                    if (qtyInput) qtyInput.value = cart[idx].qty;
                    return;
                }

                cart[idx].qty = qty;
                refreshLineTotal(productId);
                recalcSummary();
            }

            function setItemDiscount(productId, val) {
                const item = cart.find(i => i.product.id === productId);
                if (!item) return;
                item.discount_percent = Math.min(100, Math.max(0, parseFloat(val) || 0));
                refreshLineTotal(productId);
                recalcSummary();
            }

            function removeFromCart(productId) {
                cart = cart.filter(i => i.product.id !== productId);
                renderCart();
            }

            function clearCart() {
                if (cart.length === 0) return;

                // Ganti confirm() dengan toast konfirmasi
                showConfirmToast('Kosongkan semua item di keranjang?', () => {
                    cart = [];
                    renderCart();
                    toast('Keranjang dikosongkan', 'info', 2000);
                });
            }

            function showConfirmToast(message, onConfirm) {
                const container = document.getElementById('toast-container');
                const id = 'confirm-' + Date.now();

                const el = document.createElement('div');
                el.id = id;
                el.className = 'toast-item';
                el.style.cssText = `
                    min-width:280px;max-width:360px;
                    background:#fffbeb;
                    border:1px solid #fde68a;
                    border-radius:12px;
                    padding:14px;
                    box-shadow:0 4px 16px rgba(0,0,0,0.12);
                `;
                el.innerHTML = `
                    <div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:12px;">
                        <svg style="width:20px;height:20px;color:#f59e0b;flex-shrink:0;margin-top:1px;"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667
                                    1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464
                                    0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p style="margin:0;font-size:13px;color:#92400e;line-height:1.5;">${message}</p>
                    </div>
                    <div style="display:flex;gap:8px;justify-content:flex-end;">
                        <button onclick="dismissToast('${id}')"
                                style="padding:6px 14px;font-size:12px;border:1px solid #e5e7eb;
                                    border-radius:8px;background:#fff;cursor:pointer;color:#6b7280;">
                            Batal
                        </button>
                        <button onclick="confirmAction('${id}')"
                                style="padding:6px 14px;font-size:12px;font-weight:600;border:none;
                                    border-radius:8px;background:#ef4444;cursor:pointer;color:#fff;">
                            Ya, Kosongkan
                        </button>
                    </div>
                `;

                // Simpan callback
                el._onConfirm = onConfirm;
                container.appendChild(el);

                // Auto dismiss setelah 8 detik
                el._timer = setTimeout(() => dismissToast(id), 8000);
            }

            function confirmAction(id) {
                const el = document.getElementById(id);
                if (!el) return;
                const cb = el._onConfirm;
                dismissToast(id);
                if (typeof cb === 'function') cb();
            }

            // ═════════════════════════════════════════════════════════════
            // KERANJANG — RENDER
            // ═════════════════════════════════════════════════════════════

            // Update subtotal satu baris saja — tanpa full re-render
            function refreshLineTotal(productId) {
                const item = cart.find(i => i.product.id === productId);
                if (!item) return;

                const lineTotal = item.price * item.qty * (1 - item.discount_percent / 100);
                const el = document.getElementById(`line-total-${productId}`);
                if (el) {
                    el.textContent = 'Rp ' + lineTotal.toLocaleString('id-ID', {
                        maximumFractionDigits: 0
                    });
                }
            }

            function renderCart() {
                const container = document.getElementById('cart-items');

                // State kosong
                if (cart.length === 0) {
                    container.innerHTML = `
                    <div style="display:flex;flex-direction:column;align-items:center;
                                justify-content:center;height:100%;color:#d1d5db;padding:48px 0;">
                        <svg style="width:48px;height:48px;margin-bottom:8px;"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293
                                    c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4
                                    zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p style="font-size:13px;">Keranjang kosong</p>
                    </div>`;
                    recalcSummary();
                    return;
                }

                container.innerHTML = cart.map(item => {
                    const lineTotal = item.price * item.qty * (1 - item.discount_percent / 100);
                    return `
                    <div style="padding:10px 16px;border-bottom:1px solid #f3f4f6;">

                        {{-- Nama + tombol hapus --}}
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
                            <div style="flex:1;min-width:0;">
                                <p style="font-size:13px;font-weight:600;color:#111827;
                                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    ${item.product.name}
                                </p>
                                <p style="font-size:11px;color:#9ca3af;margin-top:1px;">
                                    @ Rp ${item.price.toLocaleString('id-ID')}
                                </p>
                            </div>
                            <button onclick="removeFromCart(${item.product.id})"
                                    style="background:none;border:none;cursor:pointer;
                                        color:#d1d5db;flex-shrink:0;padding:2px;line-height:1;"
                                    onmouseover="this.style.color='#ef4444'"
                                    onmouseout="this.style.color='#d1d5db'">
                                <svg style="width:14px;height:14px;"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Qty + diskon + subtotal --}}
                        <div style="display:flex;align-items:center;justify-content:space-between;
                                    margin-top:8px;gap:6px;">

                            {{-- Qty control --}}
                            <div style="display:flex;align-items:center;gap:4px;">
                                <button onclick="updateQty(${item.product.id}, -1)"
                                        style="width:24px;height:24px;border-radius:50%;background:#f3f4f6;
                                            border:1px solid #e5e7eb;cursor:pointer;font-size:15px;
                                            font-weight:700;display:flex;align-items:center;
                                            justify-content:center;color:#374151;line-height:1;
                                            flex-shrink:0;"
                                        onmouseover="this.style.background='#e5e7eb'"
                                        onmouseout="this.style.background='#f3f4f6'">−</button>

                                <input type="number" id="qty-input-${item.product.id}"
                                    value="${item.qty}" min="1" step="1"
                                    oninput="stripDecimal(this)"
                                    onchange="setQty(${item.product.id}, this.value)"
                                    style="width:46px;text-align:center;font-size:13px;font-weight:600;
                                            border:1px solid #e5e7eb;border-radius:6px;padding:3px 4px;
                                            outline:none;">

                                <button onclick="updateQty(${item.product.id}, 1)"
                                        style="width:24px;height:24px;border-radius:50%;background:#f3f4f6;
                                            border:1px solid #e5e7eb;cursor:pointer;font-size:15px;
                                            font-weight:700;display:flex;align-items:center;
                                            justify-content:center;color:#374151;line-height:1;
                                            flex-shrink:0;"
                                        onmouseover="this.style.background='#e5e7eb'"
                                        onmouseout="this.style.background='#f3f4f6'">+</button>

                                <span style="font-size:11px;color:#9ca3af;margin-left:2px;">
                                    ${item.product.unit}
                                </span>
                            </div>

                            {{-- Diskon per item --}}
                            <div style="display:flex;align-items:center;gap:3px;">
                                <input type="number" value="${item.discount_percent}"
                                    min="0" max="100" step="1" placeholder="0"
                                    onchange="setItemDiscount(${item.product.id}, this.value)"
                                    style="width:40px;text-align:center;font-size:11px;
                                            border:1px solid #e5e7eb;border-radius:6px;
                                            padding:3px 4px;outline:none;">
                                <span style="font-size:11px;color:#9ca3af;">%</span>
                            </div>

                            {{-- Subtotal baris — id unik per produk --}}
                            <p id="line-total-${item.product.id}"
                            style="font-size:13px;font-weight:700;color:#111827;
                                    white-space:nowrap;min-width:80px;text-align:right;">
                                Rp ${lineTotal.toLocaleString('id-ID', { maximumFractionDigits: 0 })}
                            </p>
                        </div>
                    </div>`;
                }).join('');

                recalcSummary();
            }

            // ═════════════════════════════════════════════════════════════
            // KALKULASI
            // ═════════════════════════════════════════════════════════════
            function recalcSummary() {
                const subtotal = cart.reduce((s, i) =>
                    s + i.price * i.qty * (1 - i.discount_percent / 100), 0);

                const discount = parseFloat(document.getElementById('discount-input').value) || 0;
                const taxPct = parseFloat(document.getElementById('tax-input').value) || 0;
                const tax = subtotal * (taxPct / 100);
                const total = Math.max(0, subtotal - discount + tax);
                currentTotal = total;

                document.getElementById('summary-subtotal').textContent =
                    'Rp ' + subtotal.toLocaleString('id-ID', {
                        maximumFractionDigits: 0
                    });
                document.getElementById('summary-total').textContent =
                    'Rp ' + total.toLocaleString('id-ID', {
                        maximumFractionDigits: 0
                    });

                const method = document.getElementById('payment-method').value;
                if (method !== 'cash') {
                    document.getElementById('paid-input').value = Math.ceil(total);
                }

                recalcChange();
            }

            function recalcChange() {
                const paid = parseFloat(document.getElementById('paid-input').value) || 0;
                const change = Math.max(0, paid - currentTotal);
                document.getElementById('change-display').textContent =
                    'Rp ' + change.toLocaleString('id-ID', {
                        maximumFractionDigits: 0
                    });
            }

            function setPaid(nominal) {
                const input = document.getElementById('paid-input');
                input.value = (parseFloat(input.value) || 0) + nominal;
                recalcChange();
            }

            function setPaidExact() {
                document.getElementById('paid-input').value = Math.ceil(currentTotal);
                recalcChange();
            }

            document.getElementById('payment-method').addEventListener('change', function() {
                const wrap      = document.getElementById('cash-input-wrap');
                const shortcuts = document.getElementById('cash-shortcuts');
                const info      = document.getElementById('midtrans-info');
                if (this.value === 'cash') {
                    wrap.style.display = 'block';
                    shortcuts.style.display = 'flex';
                    if (info) info.style.display = 'none';
                } else {
                    wrap.style.display = 'none';
                    shortcuts.style.display = 'none';
                    if (info) info.style.display = midtransEnabled ? 'block' : 'none';
                    document.getElementById('paid-input').value = Math.ceil(currentTotal);
                }
                recalcSummary();
            });

            // ═════════════════════════════════════════════════════════════
            // SUBMIT TRANSAKSI
            // ═════════════════════════════════════════════════════════════
            function buildCartPayload(method) {
                return {
                    customer_id:     document.getElementById('customer-select').value || null,
                    payment_method:  method,
                    paid_amount:     parseFloat(document.getElementById('paid-input').value) || 0,
                    discount_amount: parseFloat(document.getElementById('discount-input').value) || 0,
                    tax_percent:     parseFloat(document.getElementById('tax-input').value) || 0,
                    items: cart.map(i => ({
                        product_id:       i.product.id,
                        qty:              i.qty,
                        sale_price:       i.price,
                        discount_percent: i.discount_percent,
                        name:             i.product.name,
                    })),
                };
            }

            function setPayBtn(loading) {
                const btn = document.getElementById('btn-pay');
                btn.disabled = loading;
                btn.textContent = loading ? 'Memproses...' : 'Bayar Sekarang';
                btn.style.background = loading ? '#93c5fd' : '#2563eb';
            }

            function validateCart() {
                if (cart.length === 0) { toast('Keranjang masih kosong!', 'warning'); return false; }
                for (const item of cart) {
                    if (item.product.stock <= 0) {
                        toast(`Stok ${item.product.name} sudah habis.`, 'error'); return false;
                    }
                    if (item.qty > item.product.stock) {
                        toast(`Stok ${item.product.name} tidak cukup.\nDibutuhkan: ${item.qty} ${item.product.unit}\nTersedia: ${item.product.stock} ${item.product.unit}`, 'error');
                        return false;
                    }
                }
                return true;
            }

            async function submitTransaction() {
                if (!validateCart()) return;

                const method = document.getElementById('payment-method').value;
                const paid   = parseFloat(document.getElementById('paid-input').value) || 0;

                if (method === 'cash' && paid < currentTotal) {
                    toast('Uang yang diterima kurang!', 'warning'); return;
                }

                // Non-cash + Midtrans enabled → gunakan Snap
                if (method !== 'cash' && midtransEnabled) {
                    await submitViaMidtrans(method);
                    return;
                }

                // Cash atau Midtrans tidak aktif → flow lama
                setPayBtn(true);
                const payload = buildCartPayload(method);

                try {
                    const res  = await fetch(storeUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify(payload),
                    });
                    const data = await res.json();
                    if (data.success) showSuccessModal(data);
                    else toast(data.message, 'error', 6000);
                } catch (err) {
                    toast('Terjadi kesalahan koneksi. Coba lagi.', 'error');
                } finally {
                    setPayBtn(false);
                }
            }

            // ═════════════════════════════════════════════════════════════
            // STATE AKTIF MIDTRANS (untuk resume)
            // ═════════════════════════════════════════════════════════════
            let activeSnapToken     = null;
            let activeTransactionId = null;
            let activeMethod        = null;
            let activeAmount        = 0;
            let snapCallbacks       = null;

            const methodLabels = {
                qris:        'QRIS',
                transfer:    'Transfer Bank',
                debit_card:  'Kartu Debit',
                credit_card: 'Kartu Kredit',
            };

            // ═════════════════════════════════════════════════════════════
            // MIDTRANS SNAP FLOW
            // ═════════════════════════════════════════════════════════════
            async function submitViaMidtrans(method) {
                setPayBtn(true);

                try {
                    const payload = buildCartPayload(method);
                    const res     = await fetch(snapTokenUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify(payload),
                    });
                    const data = await res.json();

                    if (!data.success) {
                        toast(data.message || 'Gagal membuat token pembayaran', 'error', 6000);
                        setPayBtn(false);
                        return;
                    }

                    // Simpan state aktif untuk keperluan resume
                    activeSnapToken     = data.snap_token;
                    activeTransactionId = data.transaction_id;
                    activeMethod        = method;
                    activeAmount        = currentTotal;

                    setPayBtn(false);
                    openSnap();
                } catch (err) {
                    toast('Terjadi kesalahan koneksi. Coba lagi.', 'error');
                    setPayBtn(false);
                }
            }

            function openSnap() {
                snapCallbacks = {
                    onSuccess: async function(result) {
                        const tid = activeTransactionId;
                        clearActivePayment();
                        const r = await fetch(`${paymentCompleteBase}/${tid}`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                            body: JSON.stringify(result),
                        });
                        const d = await r.json();
                        if (d.success) showSuccessModal(d);
                        else toast(d.message || 'Pembayaran berhasil namun gagal dikonfirmasi, hubungi admin.', 'error', 8000);
                    },
                    onPending: function(result) {
                        // Jangan cancel — tampilkan modal info, tunggu webhook
                        showPendingModal(result);
                    },
                    onError: async function(result) {
                        const tid = activeTransactionId;
                        clearActivePayment();
                        toast('Pembayaran gagal: ' + (result.status_message || 'Error tidak diketahui'), 'error', 6000);
                        if (tid) {
                            await fetch(`${paymentCancelBase}/${tid}`, {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                            });
                        }
                    },
                    onClose: function() {
                        // Popup ditutup — JANGAN langsung cancel, tanyakan ke kasir
                        showResumeModal();
                    },
                };

                window.snap.pay(activeSnapToken, snapCallbacks);
            }

            // ─── Buka ulang Snap dengan token yang sama ───────────────────
            function resumePayment() {
                document.getElementById('modal-payment-resume').style.display = 'none';
                if (activeSnapToken) {
                    window.snap.pay(activeSnapToken, snapCallbacks);
                }
            }

            // ─── Batalkan dan hapus transaksi pending ─────────────────────
            async function cancelActivePayment() {
                const tid = activeTransactionId;
                document.getElementById('modal-payment-resume').style.display  = 'none';
                document.getElementById('modal-payment-pending').style.display = 'none';
                clearActivePayment();

                if (tid) {
                    try {
                        await fetch(`${paymentCancelBase}/${tid}`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        });
                    } catch (_) {}
                }
                toast('Transaksi dibatalkan.', 'warning', 3000);
            }

            // ─── Reset state aktif ────────────────────────────────────────
            function clearActivePayment() {
                activeSnapToken     = null;
                activeTransactionId = null;
                activeMethod        = null;
                activeAmount        = 0;
                snapCallbacks       = null;
            }

            // ─── Modal: popup ditutup tanpa bayar ─────────────────────────
            function showResumeModal() {
                document.getElementById('resume-method-label').textContent =
                    methodLabels[activeMethod] || activeMethod;
                document.getElementById('resume-amount').textContent =
                    'Rp ' + activeAmount.toLocaleString('id-ID', { maximumFractionDigits: 0 });

                const modal = document.getElementById('modal-payment-resume');
                modal.style.display = 'flex';
            }

            // ─── Modal: pembayaran pending (transfer VA dll) ──────────────
            function showPendingModal(result) {
                const info = document.getElementById('pending-payment-info');
                const lines = [];

                if (result.va_numbers && result.va_numbers.length > 0) {
                    result.va_numbers.forEach(va => {
                        lines.push(`<b>Bank:</b> ${va.bank.toUpperCase()}`);
                        lines.push(`<b>No. Virtual Account:</b> ${va.va_number}`);
                    });
                }
                if (result.payment_type) {
                    lines.push(`<b>Metode:</b> ${result.payment_type}`);
                }
                const totalAmt = result.gross_amount
                    ? 'Rp ' + parseFloat(result.gross_amount).toLocaleString('id-ID', { maximumFractionDigits: 0 })
                    : 'Rp ' + activeAmount.toLocaleString('id-ID', { maximumFractionDigits: 0 });
                lines.push(`<b>Total:</b> ${totalAmt}`);

                info.innerHTML = lines.join('<br>');

                document.getElementById('modal-payment-pending').style.display = 'flex';
            }

            // ═════════════════════════════════════════════════════════════
            // MODAL SUKSES
            // ═════════════════════════════════════════════════════════════
            function showSuccessModal(data) {
                document.getElementById('modal-sale-number').textContent = data.sale_number;
                document.getElementById('modal-total').textContent =
                    'Rp ' + parseFloat(data.total).toLocaleString('id-ID', {
                        maximumFractionDigits: 0
                    });
                document.getElementById('modal-paid').textContent =
                    'Rp ' + parseFloat(data.paid).toLocaleString('id-ID', {
                        maximumFractionDigits: 0
                    });
                document.getElementById('modal-change').textContent =
                    'Rp ' + parseFloat(data.change).toLocaleString('id-ID', {
                        maximumFractionDigits: 0
                    });
                document.getElementById('btn-receipt').href = data.receipt_url;

                const modal = document.getElementById('modal-success');
                modal.style.display = 'flex';
            }

            function newTransaction() {
                cart = [];
                clearActivePayment();
                renderCart();
                document.getElementById('customer-select').value = '';
                document.getElementById('discount-input').value = '0';
                document.getElementById('tax-input').value = '0';
                document.getElementById('paid-input').value = '0';
                document.getElementById('payment-method').value = 'cash';
                document.getElementById('cash-input-wrap').style.display = 'block';
                document.getElementById('cash-shortcuts').style.display = 'flex';
                const info = document.getElementById('midtrans-info');
                if (info) info.style.display = 'none';
                document.getElementById('modal-success').style.display = 'none';
                document.getElementById('modal-payment-resume').style.display = 'none';
                document.getElementById('modal-payment-pending').style.display = 'none';
                document.getElementById('search-product').value = '';
                document.getElementById('search-product').focus();
                recalcSummary();
                renderProducts(allProducts);
            }

            // ── Init ──────────────────────────────────────────────────────
            renderProducts(allProducts);
        </script>

        @if($midtransEnabled && $midtransClientKey)
        <script src="{{ $midtransSnapJs }}" data-client-key="{{ $midtransClientKey }}"></script>
        @endif
    @endpush
@endsection
