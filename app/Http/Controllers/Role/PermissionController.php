<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\PermissionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sort = $request->sort ?? 10;
        $search = $request->search ?? null;

        $permissions = Permission::when($search, function ($query, $search) {
                                    return $query->where('name', 'like', "%$search%");
                                })
                                ->orderBy('id', 'DESC')
                                ->paginate($sort);

        return view("pages.permission.index", compact("permissions"));
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
        Permission::create($post);

        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'Berhasil menyimpan data.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $permissions = Permission::find($id);

        if (!$permissions) {
            return response()->json([
                'code' => 400, 
                'status' => 'error',
                'message' => 'Data Not Found.',
            ]);
        }

        return response()->json(['code' => 200, 'status' => 'success', 'data' => $permissions]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $permissions = Permission::find($id);

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
        $permissions->update($put);

        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'Berhasil mengubah data.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $permissions = Permission::find($id);

        if (!$permissions) {
            return response()->json([
                'code' => 400, 
                'status' => 'error',
                'message' => 'Data Not Found.',
            ]);
        }

        $permissions->delete();

        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'Berhasil menghapus data.']);
    }
}
