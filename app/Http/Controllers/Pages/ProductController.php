<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Categori;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sort = $request->sort ?? 10;
        $search = $request->search ?? null;

        $products = Product::with(['categori', 'branch'])
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
            ->paginate($sort);

        $categories = Categori::select(['id', 'name'])->get();
        $branch = Branch::all();

        return view("pages.product.index", compact("products", "categories", "branch"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "code" => "required|unique:products,code",
            "name" => "required|string",
            "categories_id" => "required|exists:categories,id",
            "stock" => "required|integer|min:0",
            "base_price" => "required|numeric|min:0",
            "selling_price" => "required|numeric|min:0",
            "branch_id" => "required"
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
        $post['base_price'] = str_replace('.', '', $request->base_price);
        $post['selling_price'] = str_replace('.', '', $request->selling_price);
        Product::create($post);

        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'Berhasil menyimpan data.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $products = Product::find($id);

        if (!$products) {
            return response()->json([
                'code' => 400,
                'status' => 'error',
                'message' => 'Data Not Found.',
            ]);
        }

        return response()->json(['code' => 200, 'status' => 'success', 'data' => $products]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $products = Product::find($id);

        $validation = Validator::make($request->all(), [
            "code" => "required|unique:products,code," . ($products->id ?? 'NULL') . ",id",
            "name" => "required|string",
            "categories_id" => "required|exists:categories,id",
            "stock" => "required|integer|min:0",
            "base_price" => "required|numeric|min:0",
            "selling_price" => "required|numeric|min:0",
            "branch_id" => "required"
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
        $put['base_price'] = str_replace('.', '', $request->base_price);
        $put['selling_price'] = str_replace('.', '', $request->selling_price);
        $products->update($put);

        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'Berhasil mengubah data.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $products = Product::find($id);

        if (!$products) {
            return response()->json([
                'code' => 400,
                'status' => 'error',
                'message' => 'Data Not Found.',
            ]);
        }

        $products->delete();

        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'Berhasil menghapus data.']);
    }
}
