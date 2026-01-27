<?php

// app/Http/Controllers/SupportController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Support;

class SupportController extends Controller
{
    public function index()
    {
        $supports = Support::all();
        return response()->json($supports);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'description' => 'required',
        ]);

        $support = Support::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'description' => $request->input('description'),
        ]);

        return response()->json($support, 201);
    }
}

