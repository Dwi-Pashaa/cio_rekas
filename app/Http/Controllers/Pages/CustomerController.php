<?php

namespace App\Http\Controllers\Pages;

use App\Exports\CustomerExport;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerStatus;
use App\Models\CustomerType;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

        $customers = Customer::with(['product', 'type', 'status'])
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
            ->paginate($sort);

        $products = Product::select(['id', 'code', 'name'])->get();
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
            "products_id" => "required|exists:products,id",
            "code" => "required|unique:customers,code",
            "name" => "required|string",
            "telp" => "required",
            "address" => "required|string",
            "limit" => "required|integer|min:1",
            "types_id" => "required",
            "status_id" => "required"
        ]);

        if ($validation->fails()) {
            return response()->json([
                'code' => 400,
                'status' => 'error',
                'message' => 'Opps ada yang belum di isi.',
                'errors' => $validation->errors()
            ]);
        }

        $post = $request->all();
        $post['type'] = $request->type_customer;
        Customer::create($post);

        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'Berhasil menyimpan data.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customers = Customer::with(['product', 'type', 'status'])->find($id);

        if (!$customers) {
            return response()->json([
                'code' => 400,
                'status' => 'error',
                'message' => 'Data Not Found.',
            ]);
        }

        $customers->created = Carbon::parse($customers->created_at)->translatedFormat('d/F/Y H:i:s');

        return response()->json(['code' => 200, 'status' => 'success', 'data' => $customers]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $customers = Customer::find($id);

        $validation = Validator::make($request->all(), [
            "products_id" => "required|exists:products,id",
            "code" => "required|unique:products,code," . ($customers->id ?? 'NULL') . ",id",
            "name" => "required|string",
            "telp" => "required",
            "address" => "required|string",
            "limit" => "required|integer|min:1",
            "types_id" => "required",
            "status_id" => "required"
        ]);

        if ($validation->fails()) {
            return response()->json([
                'code' => 400,
                'status' => 'error',
                'message' => 'Opps ada yang belum di isi.',
                'errors' => $validation->errors()
            ]);
        }

        $put = $request->all();
        $put['type'] = $request->type_customer;
        $customers->update($put);

        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'Berhasil mengubah data.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $customers = Customer::find($id);

        if (!$customers) {
            return response()->json([
                'code' => 400,
                'status' => 'error',
                'message' => 'Data Not Found.',
            ]);
        }

        $customers->delete();

        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'Berhasil menghapus data.']);
    }

    public function export()
    {
        return Excel::download(new CustomerExport, 'Data Pelanggan.xlsx');
    }
}
