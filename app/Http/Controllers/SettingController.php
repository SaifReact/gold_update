<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    { 
        try {
            $setting = Setting::where('id', 1)->first();
            $response = [
                'siteSetting' => [
                    'id' => $setting->id,
                    'name' => $setting->name,
                    'full_name' => $setting->full_name,
                    'about' => $setting->about,
                    'contact_no' => $setting->contact_no,
                    'address' => $setting->address,
                    'email' => $setting->email,
                    'created_at' => $setting->created_at,
                    'updated_at' => $setting->updated_at,
                ],
                'bankDetails' => [
                    'bank_name' => $setting->bank_name,
                    'account_no' => $setting->account_no,
                    'branch_name' => $setting->branch_name,
                ],
            ];
            return response()->json(['error' => null, 'data' => $response], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred', 'data' => null], 500);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function show(Setting $setting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function edit(Setting $setting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Setting $setting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function destroy(Setting $setting)
    {
        //
    }
}
