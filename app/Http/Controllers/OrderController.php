<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $loggedInUser = $request->user();
            $userId = Auth::id();
            if ($loggedInUser->tokenCan('admin')) {
                $orderData = DB::table('orders')
                    ->join('products', 'orders.product_id', '=', 'products.id')
                    ->select('orders.id', 'orders.user_id', 'orders.product_id', 'orders.status', 'orders.created_at', 'orders.updated_at', 'products.product_name', 'products.purity', 'products.shape', 'products.product_image')
                    ->get();
            } else {
                $orderData = DB::table('orders')
                    ->join('products', 'orders.product_id', '=', 'products.id')
                    ->where('orders.user_id', $userId)
                    ->select('orders.id', 'orders.user_id', 'orders.product_id', 'orders.status', 'orders.created_at', 'orders.updated_at', 'products.product_name', 'products.purity', 'products.shape', 'products.product_image')
                    ->get();
            }

            return response()->json(['error' => null, 'data' => $orderData], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred', 'data' => null], 500);
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function status(Request $request)
    {
        $loggedInUser = $request->user();
        
        if ($loggedInUser->tokenCan('admin')) {
            $order = Order::findOrFail($request->id);
            $order->update(['status' => $request->status]);
            return response()->json(['message' => 'updated successfully']);
        } else {
            return response()->json(['status' => '401', 'message' => 'Not update']);
        }
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
        // return true;
        try {
            // Validate the request data
            $creds = $request->validate([
                'user_id' => 'required|numeric',
                'product_id' => 'required|numeric',
                'order_date' => 'required|date_format:d/m/Y',
            ]);

            // Create a new instance of the Order model
            $newsAlart = new Order();

            // Set the model's attributes with the validated data
            $newsAlart->user_id = $creds['user_id'];
            $newsAlart->product_id = $creds['product_id'];
            $newsAlart->order_date = \Carbon\Carbon::createFromFormat('d/m/Y', $creds['order_date'])->format('Y-m-d');

            // Save the model to the database
            if ($newsAlart->save()) {
                return response()->json([
                    'error' => 0,
                    'message' => 'Order successfully',
                    'data' => $newsAlart,
                ], 201);
            } else {
                // Database save failed
                return response([
                    'error' => 1,
                    'message' => 'Failed to save order to the database',
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
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }

    public function goldTrends()
    {
        try {
            $response = [
                'percent' => 80,
                'sell_below' => 50000,
                'profit_around' => 59700,
                'entry_price' => 57000,
                'stop_loss' => 56000,
                'target1' => 53000,
                'target2' => 54000,
            ];
            return response()->json(['error' => null, 'data' => $response], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred', 'data' => null], 500);
        }
    }
}
