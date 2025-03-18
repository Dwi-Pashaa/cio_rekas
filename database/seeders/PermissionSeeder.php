<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'tambah level', 'lihat level', 'edit level', 'hapus level',
            'tambah user', 'lihat user', 'edit user', 'hapus user',
            'tambah kategori', 'lihat kategori', 'edit kategori', 'hapus kategori',
            'tambah barang', 'lihat barang', 'edit barang', 'hapus barang',
            'tambah pelanggan', 'lihat pelanggan', 'edit pelanggan', 'hapus pelanggan',
            'tambah transaksi', 'lihat transaksi', 'edit transaksi', 'hapus transaksi',
            'lihat keuangan',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
