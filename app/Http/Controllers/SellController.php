<?php

namespace App\Http\Controllers;

use App\Models\Sell;
use Illuminate\Http\Request;

class SellController extends Controller
{
    public function index()
    {
        $sells = Sell::all();
        return response()->json($sells);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sellDate' => 'required',
            'price' => 'required',
        ]);

        $sell = sell::create([
            'sellDate' => $request->input('sellDate'),
            'price' => $request->input('price'),
        ]);

        return response()->json($sell, 201);
    }
}
