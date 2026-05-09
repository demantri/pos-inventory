<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── Kategori produk ──
        $categories = [
            ['name' => 'Makanan & Minuman',  'description' => 'Produk konsumsi sehari-hari'],
            ['name' => 'Alat Tulis Kantor',  'description' => 'Kebutuhan kantor dan sekolah'],
            ['name' => 'Elektronik',         'description' => 'Produk elektronik dan aksesoris'],
            ['name' => 'Kebersihan',         'description' => 'Produk kebersihan rumah tangga'],
            ['name' => 'Kesehatan',          'description' => 'Produk kesehatan dan obat-obatan'],
            ['name' => 'Fashion',            'description' => 'Pakaian dan aksesoris'],
            ['name' => 'Lain-lain',          'description' => 'Produk umum lainnya'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                ['name' => $cat['name'], 'description' => $cat['description'], 'is_active' => true]
            );
        }

        // ── Satuan ──
        $units = [
            ['name' => 'Pieces',    'symbol' => 'pcs'],
            ['name' => 'Kilogram',  'symbol' => 'kg'],
            ['name' => 'Gram',      'symbol' => 'gr'],
            ['name' => 'Liter',     'symbol' => 'ltr'],
            ['name' => 'Mililiter', 'symbol' => 'ml'],
            ['name' => 'Box',       'symbol' => 'box'],
            ['name' => 'Lusin',     'symbol' => 'lsn'],
            ['name' => 'Rim',       'symbol' => 'rim'],
            ['name' => 'Pack',      'symbol' => 'pck'],
            ['name' => 'Botol',     'symbol' => 'btl'],
            ['name' => 'Karung',    'symbol' => 'krg'],
            ['name' => 'Meter',     'symbol' => 'm'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['symbol' => $unit['symbol']], $unit + ['is_active' => true]);
        }

        $this->command->info('✅ Master data (kategori & satuan) berhasil dibuat.');
    }
}
