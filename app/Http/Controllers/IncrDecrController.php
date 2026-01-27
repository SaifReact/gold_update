<?php

namespace App\Http\Controllers;

use App\Models\IncrDecr;
use Illuminate\Http\Request;

class IncrDecrController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $historicalData = IncrDecr::orderBy('created_at', 'desc')
        ->get();
        return response()->json($historicalData);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        {
            // Validate the request data (you can add more validation rules as needed)
            $request->validate([
                'type_id' => 'required',
                'type' => 'required',
                'money' => 'required',
                'incr' => 'required',
                'decr' => 'required',
            ]);
    
            // Create a new IncrDecr instance and save it to the database
            $data = IncrDecr::create($request->all());
    
            // Return a JSON response with the newly created data and a success message
            return response()->json([
                'data' => $data,
                'message' => 'Data inserted successfully',
            ], 201); // 201 Created status code
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\IncrDecr  $incrDecr
     * @return \Illuminate\Http\Response
     */
    public function show(IncrDecr $incrDecr)
    {
        dd('work put');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\IncrDecr  $incrDecr
     * @return \Illuminate\Http\Response
     */
    public function edit(IncrDecr $incrDecr)
    {
        dd('work put');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\IncrDecr  $incrDecr
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $loggedInUser = $request->user();
        
        if ($loggedInUser->tokenCan('admin')) {
            $order = IncrDecr::findOrFail($request->id);
            $order->update(['incr' => $request->incr]);
            $order->update(['decr' => $request->decr]);
            return response()->json(['message' => 'updated successfully']);
        } else {
            return response()->json(['status' => '401', 'message' => 'Not update']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\IncrDecr  $incrDecr
     * @return \Illuminate\Http\Response
     */
    public function destroy(IncrDecr $incrDecr)
    {
        dd('work put');
    }
}
