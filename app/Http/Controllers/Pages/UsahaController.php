<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Usaha;
use App\Models\User;
use Illuminate\Http\Request;

class UsahaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usaha = Usaha::latest()->first();
        return view("pages.setting.index", compact("usaha"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "id" => "nullable|exists:usahas,id",
            "name" => "required|string",
            "address" => "required|string",
            "name_of_thermal" => "required|string",
            "footer" => "required",
            "image" => "nullable|mimes:png"
        ]);
        
        $post = $request->except('image');
        
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = rand() . '.' . $file->getClientOriginalExtension();
            $path = 'img/logo/';
            $file->move(public_path($path), $fileName);
            $post['image'] = $path . $fileName;
        }
        
        Usaha::updateOrCreate(
            ['id' => $request->id],
            $post
        );
        
        return back()->with('success', 'Data berhasil diperbarui atau ditambahkan.');
    }
}
