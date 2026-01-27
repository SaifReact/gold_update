<?php

namespace App\Http\Controllers;

use App\Models\AlartTable;
use Illuminate\Http\Request;

class AlartTableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $response = AlartTable::where('status', 1)->get();
        return response()->json($response);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function create()
    // {
    //     //
    // }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validate the request data
            $creds = $request->validate([
                'gold_weight' => 'required',
                'app_price' => 'required|numeric',
                'offer_price' => 'required|numeric',
                'Name' => 'required|string',
                'Mobile_no' => 'required|string',
                'buy_date' => 'required|date_format:d/m/Y',
                'status' => 'required|boolean',
            ]);
        
            // Create a new instance of the NewsAlart model
            $newsAlart = new AlartTable();
        
            // Set the model's attributes with the validated data
            $newsAlart->gold_weight = $creds['gold_weight'];
            $newsAlart->app_price = $creds['app_price'];
            $newsAlart->offer_price = $creds['offer_price'];
            $newsAlart->Name = $creds['Name'];
            $newsAlart->Mobile_no = $creds['Mobile_no'];
            $newsAlart->buy_date = \Carbon\Carbon::createFromFormat('d/m/Y', $creds['buy_date'])->format('Y-m-d');
            $newsAlart->status = $creds['status'];
        
            // Save the model to the database
            if ($newsAlart->save()) {
                return response()->json([
                    'error' => 0,
                    'data' => $newsAlart,
                    'message' => 'Record created successfully',
                ], 201);
            } else {
                // Database save failed
                return response([
                    'error' => 1,
                    'data' => '',
                    'message' => 'Failed to save data to the database',
                ], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation failed
            return response([
                'error' => 1,
                'data' => $e->errors(), // Return validation error messages
                'message' => 'Validation Error',
            ], 422);
        } catch (\Exception $e) {
            // Other unexpected errors
            return response([
                'error' => 1,
                'data' => '',
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AlartTable  $alartTable
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $resource = AlartTable::find($id);

        if (!$resource) {
            return response()->json(['message' => 'Rate Alart Record not found'], 404);
        }

        return response()->json(['data' => $resource], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AlartTable  $alartTable
     * @return \Illuminate\Http\Response
     */
    // public function edit(AlartTable $alartTable)
    // {
    //     dd($alartTable);
    //     $response = AlartTable::where('id', $alartTable)->get();
    //     return response()->json($response);
    // }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AlartTable  $alartTable
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Find the resource by its ID
        $resource = AlartTable::find($id);

        if (!$resource) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        // Validate the request data
        $data = $request->validate([
            'status' => 'required|boolean',
        ]);

        // Update the status field
        $resource->status = $data['status'];
        $resource->save();

        return response()->json(['message' => 'Status updated successfully', 'data' => $resource], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AlartTable  $alartTable
     * @return \Illuminate\Http\Response
     */
    public function destroy(AlartTable $alartTable)
    {
        //
    }
}
