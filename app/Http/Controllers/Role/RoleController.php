<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sort = $request->sort ?? 10;
        $search = $request->search ?? null;

        $roles = Role::with('permissions')->when($search, function ($query, $search) {
                                    return $query->where('name', 'like', "%$search%");
                                })
                                ->orderBy('id', 'DESC')
                                ->paginate($sort);

        return view("pages.roles.index", compact("roles"));
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
        Role::create($post);

        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'Berhasil menyimpan data.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'code' => 400, 
                'status' => 'error',
                'message' => 'Data Not Found.',
            ]);
        }

        return response()->json(['code' => 200, 'status' => 'success', 'data' => $role]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::find($id);

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
        $role->update($put);

        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'Berhasil mengubah data.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'code' => 400, 
                'status' => 'error',
                'message' => 'Data Not Found.',
            ]);
        }

        $role->delete();

        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'Berhasil menghapus data.']);
    }

    public function permission(string $id) 
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::all(); 
        return view("pages.roles.permission", compact("role", "permissions"));
    }

    public function savePermission(Request $request, string $id) 
    {
        $request->validate([
            "permissions" => "required"
        ]);

        $role = Role::findOrFail($id);
        $role->syncPermissions($request->permissions);
        return back()->with('success', 'Berhasil menyimpan aksess untuk role ' . $role->name);     
    }
}
