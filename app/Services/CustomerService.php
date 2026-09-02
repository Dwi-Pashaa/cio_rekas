<?php

namespace App\Services;

use App\Models\Customer;

class CustomerService
{
    /**
     * Ambil list pelanggan dengan pagination dan pencarian.
     */
    public function getCustomersPaginated(?string $search = null, int $perPage = 10)
    {
        return Customer::with(['product', 'type', 'status'])
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%")
                    ->orWhere('stock', 'like', "%$search%")
                    ->orWhereHas('product', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%")
                            ->orWhere('code', 'like', "%$search%");
                    });
            })
            ->orderBy('id', 'DESC')
            ->paginate($perPage);
    }

    /**
     * Simpan pelanggan baru.
     */
    public function storeCustomer(array $data)
    {
        return Customer::create($data);
    }

    /**
     * Update data pelanggan.
     */
    public function updateCustomer(int|string $id, array $data)
    {
        $customer = Customer::findOrFail($id);
        $customer->update($data);
        return $customer;
    }

    /**
     * Hapus data pelanggan.
     */
    public function deleteCustomer(int|string $id): bool
    {
        $customer = Customer::findOrFail($id);
        return $customer->delete();
    }

    /**
     * Detail pelanggan by ID.
     */
    public function getCustomerById(int|string $id)
    {
        return Customer::with(['product', 'type', 'status'])->findOrFail($id);
    }
}
