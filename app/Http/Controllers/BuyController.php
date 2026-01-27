<?php

namespace App\Http\Controllers;

use App\Models\Buy;
use Illuminate\Http\Request;

class BuyController extends Controller
{
    public function index()
    {
        $buys = Buy::all();
        return response()->json($buys);
    }

    public function store(Request $request)
    {
        $request->validate([
            'buyDate' => 'required',
            'price' => 'required',
        ]);

        $buy = Buy::create([
            'buyDate' => $request->input('buyDate'),
            'price' => $request->input('price'),
        ]);

        return response()->json($buy, 201);
    }
}
