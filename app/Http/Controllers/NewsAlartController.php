<?php

namespace App\Http\Controllers;

use App\Models\NewsAlart;
use Illuminate\Http\Request;

class NewsAlartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $response = NewsAlart::where('status', 1)->get();
        return response()->json($response);
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
        try {
            // Validate the request data
            $creds = $request->validate([
                'title' => 'required',
                'description' => 'required'
            ]);

            // Create a new instance of the Order model
            $newsAlart = new NewsAlart();

            $newsAlart->title = $creds['title'];
            $newsAlart->description = $creds['description'];
            $newsAlart->date = \Carbon\Carbon::now()->format('Y-m-d');
            $newsAlart->status = true;

            // Save the model to the database
            if ($newsAlart->save()) {
                return response()->json([
                    'error' => 0,
                    'message' => 'News alart create successfully',
                    'data' => $newsAlart,
                ], 201);
            } else {
                // Database save failed
                return response([
                    'error' => 1,
                    'message' => 'Failed to save News alart to the database',
                    'data' => '',
                ], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation failed
            return response([
                'error' => 1,
                'message' => 'Validation Error',
                'data' => $e->errors(), // Return validation error messages
            ], 422);
        } catch (\Exception $e) {
            // Other unexpected errors
            return response([
                'error' => 1,
                'message' => 'An error occurred: ' . $e->getMessage(),
                'data' => '',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\NewsAlart  $newsAlart
     * @return \Illuminate\Http\Response
     */
    public function show(NewsAlart $newsAlart)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\NewsAlart  $newsAlart
     * @return \Illuminate\Http\Response
     */
    public function edit(NewsAlart $newsAlart)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\NewsAlart  $newsAlart
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NewsAlart $newsAlart)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\NewsAlart  $newsAlart
     * @return \Illuminate\Http\Response
     */
    public function destroy(NewsAlart $newsAlart)
    {
        //
    }
}
