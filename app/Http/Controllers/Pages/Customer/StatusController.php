<?php

namespace App\Http\Controllers\Pages\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sort = $request->sort ?? 10;
        $search = $request->search ?? null;

        $status = CustomerStatus::when($search, function ($query, $search) {
                                    return $query->where('name', 'like', "%$search%");
                                })
                                ->orderBy('id', 'DESC')
                                ->paginate($sort);

        return view("pages.customer.status.index", compact("status"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "name" => "required"
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
        CustomerStatus::create($post);

        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'Berhasil menyimpan data.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $status = CustomerStatus::find($id);

        if (!$status) {
            return response()->json([
                'code' => 400, 
                'status' => 'error',
                'message' => 'Data Not Found.',
            ]);
        }

        return response()->json(['code' => 200, 'status' => 'success', 'data' => $status]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $status = CustomerStatus::find($id);

        $validation = Validator::make($request->all(), [
            "name" => "required"
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
        $status->update($put);

        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'Berhasil mengubah data.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $status = CustomerStatus::find($id);

        if (!$status) {
            return response()->json([
                'code' => 400, 
                'status' => 'error',
                'message' => 'Data Not Found.',
            ]);
        }

        $status->delete();

        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'Berhasil menghapus data.']);
    }
}
