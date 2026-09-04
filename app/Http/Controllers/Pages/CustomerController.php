<?php

namespace App\Http\Controllers\Pages;

use App\Exports\CustomerExport;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerStatus;
use App\Models\CustomerType;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sort = $request->sort ?? 10;
        $search = $request->search ?? null;

        $customers = Customer::with(['user', 'product.branch', 'type', 'status'])
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('nik', 'like', "%$search%")
                    ->orWhere('telp', 'like', "%$search%")
                    ->orWhere('address', 'like', "%$search%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('username', 'like', "%$search%");
                    })
                    ->orWhereHas('product', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%")
                            ->orWhere('code', 'like', "%$search%");
                    });
            })
            ->orderBy('id', 'DESC')
            ->paginate($sort);

        $products = Product::with('branch')->select(['id', 'code', 'name', 'branch_id'])->get();
        $customerTypes = CustomerType::select(['id', 'name'])->get();
        $customerStatus = CustomerStatus::select(['id', 'name'])->get();

        return view("pages.customer.index", compact("customers", "products", "customerTypes", "customerStatus"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "products_id"      => "required|exists:products,id",
            "code"             => "required|unique:customers,code",
            "name"             => "required|string",
            "username"         => "required|string|min:3|unique:users,username",
            "password"         => "required|string|min:6",
            "telp"             => "required",
            "email"            => "required|email|max:255|unique:users,email",
            "nik"              => "nullable|string|max:30",
            "address"          => "required|string",
            "limit"            => "required|integer|min:1",
            "types_id"         => "required",
            "status_id"        => "required",
            "payment_methods"  => "required|array|min:1",
            "payment_methods.*"=> "in:cash,transfer",
        ], [
            'username.unique'          => 'Username sudah digunakan oleh akun lain.',
            'email.unique'             => 'Email sudah digunakan oleh akun lain.',
            'payment_methods.required' => 'Pilih minimal satu tipe pembayaran (Cash atau Transfer).',
            'payment_methods.min'      => 'Pilih minimal satu tipe pembayaran (Cash atau Transfer).',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'code'    => 400,
                'status'  => 'error',
                'message' => 'Opps ada isian yang belum lengkap atau tidak valid.',
                'errors'  => $validation->errors()
            ]);
        }

        try {
            DB::transaction(function () use ($request) {
                // Cari branch_id dari produk yang dipilih
                $product = Product::find($request->products_id);
                $branchId = $product ? $product->branch_id : null;

                // 1. Buat Akun User untuk Agent
                $user = User::create([
                    'username'  => $request->username,
                    'name'      => $request->name,
                    'email'     => $request->email,
                    'branch_id' => $branchId,
                    'password'  => Hash::make($request->password),
                ]);

                // Berikan role Agent (yang memiliki permission 'transaksi mandiri')
                $user->assignRole('Agent');

                // 2. Buat Data Customer yang tertaut dengan User & Opsi Pembayaran
                Customer::create([
                    'user_id'         => $user->id,
                    'products_id'     => $request->products_id,
                    'code'            => $request->code,
                    'name'            => $request->name,
                    'telp'            => $request->telp,
                    'email'           => $request->email,
                    'nik'             => $request->nik,
                    'address'         => $request->address,
                    'limit'           => $request->limit,
                    'types_id'        => $request->types_id,
                    'status_id'       => $request->status_id,
                    'payment_methods' => $request->payment_methods,
                ]);
            });

            return response()->json([
                'code'    => 200,
                'status'  => 'success',
                'message' => 'Berhasil menyimpan data agent beserta akun loginnya.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'code'    => 500,
                'status'  => 'error',
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customers = Customer::with(['user', 'product.branch', 'type', 'status'])->find($id);

        if (!$customers) {
            return response()->json([
                'code'    => 400,
                'status'  => 'error',
                'message' => 'Data Not Found.',
            ]);
        }

        $customers->created = Carbon::parse($customers->created_at)->translatedFormat('d/F/Y H:i:s');
        $customers->user_username = optional($customers->user)->username ?? '';

        return response()->json(['code' => 200, 'status' => 'success', 'data' => $customers]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $customers = Customer::with('user')->find($id);

        if (!$customers) {
            return response()->json([
                'code'    => 400,
                'status'  => 'error',
                'message' => 'Data agent tidak ditemukan.',
            ]);
        }

        $userId = $customers->user_id;

        $validation = Validator::make($request->all(), [
            "products_id"      => "required|exists:products,id",
            "code"             => "required|unique:customers,code," . $customers->id,
            "name"             => "required|string",
            "username"         => "required|string|min:3|unique:users,username," . ($userId ?? 'NULL'),
            "password"         => "nullable|string|min:6",
            "telp"             => "required",
            "email"            => "required|email|max:255|unique:users,email," . ($userId ?? 'NULL'),
            "nik"              => "nullable|string|max:30",
            "address"          => "required|string",
            "limit"            => "required|integer|min:1",
            "types_id"         => "required",
            "status_id"        => "required",
            "payment_methods"  => "required|array|min:1",
            "payment_methods.*"=> "in:cash,transfer",
        ], [
            'username.unique'          => 'Username sudah digunakan oleh akun lain.',
            'email.unique'             => 'Email sudah digunakan oleh akun lain.',
            'payment_methods.required' => 'Pilih minimal satu tipe pembayaran (Cash atau Transfer).',
            'payment_methods.min'      => 'Pilih minimal satu tipe pembayaran (Cash atau Transfer).',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'code'    => 400,
                'status'  => 'error',
                'message' => 'Opps ada isian yang belum lengkap atau tidak valid.',
                'errors'  => $validation->errors()
            ]);
        }

        try {
            DB::transaction(function () use ($request, $customers, $userId) {
                // Cari branch_id dari produk yang dipilih
                $product = Product::find($request->products_id);
                $branchId = $product ? $product->branch_id : null;

                // 1. Update / Create Akun User
                if ($userId && ($user = User::find($userId))) {
                    $userData = [
                        'name'      => $request->name,
                        'email'     => $request->email,
                        'username'  => $request->username,
                        'branch_id' => $branchId,
                    ];
                    if ($request->filled('password')) {
                        $userData['password'] = Hash::make($request->password);
                    }
                    $user->update($userData);
                    $user->syncRoles(['Agent']);
                } else {
                    $user = User::create([
                        'username'  => $request->username,
                        'name'      => $request->name,
                        'email'     => $request->email,
                        'branch_id' => $branchId,
                        'password'  => Hash::make($request->password ?: 'password123'),
                    ]);
                    $user->assignRole('Agent');
                    $customers->user_id = $user->id;
                }

                // 2. Update Data Customer
                $customers->update([
                    'user_id'         => $customers->user_id ?? $user->id,
                    'products_id'     => $request->products_id,
                    'code'            => $request->code,
                    'name'            => $request->name,
                    'telp'            => $request->telp,
                    'email'           => $request->email,
                    'nik'             => $request->nik,
                    'address'         => $request->address,
                    'limit'           => $request->limit,
                    'types_id'        => $request->types_id,
                    'status_id'       => $request->status_id,
                    'payment_methods' => $request->payment_methods,
                ]);
            });

            return response()->json([
                'code'    => 200,
                'status'  => 'success',
                'message' => 'Berhasil memperbarui data agent beserta akun loginnya.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'code'    => 500,
                'status'  => 'error',
                'message' => 'Gagal memperbarui data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $customers = Customer::find($id);

        if (!$customers) {
            return response()->json([
                'code'    => 400,
                'status'  => 'error',
                'message' => 'Data Not Found.',
            ]);
        }

        try {
            DB::transaction(function () use ($customers) {
                $userId = $customers->user_id;
                $customers->delete();

                if ($userId && ($user = User::find($userId))) {
                    $user->delete();
                }
            });

            return response()->json(['code' => 200, 'status' => 'success', 'message' => 'Berhasil menghapus data agent dan akun loginnya.']);
        } catch (Exception $e) {
            return response()->json(['code' => 500, 'status' => 'error', 'message' => 'Gagal menghapus data: ' . $e->getMessage()]);
        }
    }

    public function export()
    {
        return Excel::download(new CustomerExport, 'Data Pelanggan.xlsx');
    }
}
