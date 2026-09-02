<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    /**
     * Ambil list produk terpaginasi dengan filter pencarian.
     */
    public function getProductsPaginated(?string $search = null, int $perPage = 10)
    {
        return Product::with(['categori', 'branch'])
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%")
                    ->orWhere('stock', 'like', "%$search%")
                    ->orWhereHas('categori', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%");
                    })
                    ->orWhereHas('branch', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%");
                    });
            })
            ->orderBy('id', 'DESC')
            ->paginate($perPage);
    }

    /**
     * Simpan produk baru.
     */
    public function storeProduct(array $data)
    {
        return Product::create($data);
    }

    /**
     * Update produk yang sudah ada.
     */
    public function updateProduct(int|string $id, array $data)
    {
        $product = Product::findOrFail($id);
        $product->update($data);
        return $product;
    }

    /**
     * Hapus produk.
     */
    public function deleteProduct(int|string $id): bool
    {
        $product = Product::findOrFail($id);
        return $product->delete();
    }

    /**
     * Ambil produk berdasarkan ID.
     */
    public function getProductById(int|string $id)
    {
        return Product::with(['categori', 'branch'])->findOrFail($id);
    }
}
