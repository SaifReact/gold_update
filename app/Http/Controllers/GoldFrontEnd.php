<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\LiveDataHour;
use App\Models\LiveRateData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;

class GoldFrontEnd extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Models\GoldFrontEnd  $goldFrontEnd
     * @return \Illuminate\Http\Response
     */
    public function show(GoldFrontEnd $goldFrontEnd)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GoldFrontEnd  $goldFrontEnd
     * @return \Illuminate\Http\Response
     */
    public function edit(GoldFrontEnd $goldFrontEnd)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GoldFrontEnd  $goldFrontEnd
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GoldFrontEnd $goldFrontEnd)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GoldFrontEnd  $goldFrontEnd
     * @return \Illuminate\Http\Response
     */
    public function destroy(GoldFrontEnd $goldFrontEnd)
    {
        //
    }    

    public function fetchBroadcastData()
    {
        try {
            $response = Http::get('http://bcast.apanjewellery.com:7767/VOTSBroadcastStreaming/Services/xml/GetLiveRateByTemplateID/apan', [
                '_' => time()
            ]);
            // dd($response);
            $data = $response->body(); // Get the plain text data
            $rows = explode("\n", $data); // Split data into rows

            $result = [];

            foreach ($rows as $row) {
                $columns = explode("\t", trim($row)); // Split row into columns
                if (count($columns) >= 4) {
                    $result[] = [
                        'id' => $columns[0],
                        'type' => $columns[1],
                        'bid_sell' => $columns[2],
                        'ask_buy' => $columns[3],
                        'high' => $columns[4],
                        'low' => $columns[5]
                    ];
                }
            }

            return $result;
        } catch (RequestException $e) {
            // Handle the exception, log the error, and provide a user-friendly message
            Log::error('Error connecting to the server: ' . $e->getMessage());
            return response()->json(['error' => 'Could not fetch data from the server'], 500);
        }
    }

    public function showBroadcastData()
    {
        $broadcastData = $this->fetchBroadcastData();

        if (isset($broadcastData['error'])) {
            return response()->json($broadcastData, 500);
        }

        return response()->json($broadcastData);
    }

    // historical data part 
    public function type()
    {
        return [
            'GOLD OZ', 'GOLD PURE 92', 'GOLD 9999', 'TEN TOLA BAR', 'KILO BAR 995', 'KILO BAR 9999'
        ];
    }

    public function getHistoricalData(Request $request)
    {
        $type = $request->query('type', null);
        $endDate = $request->query('endDate', '2023-08-25'); // Default date
        $days = $request->query('days', 7); // Default number of days

     if($type){
        $historicalData = LiveRateData::select('type','bid_sell', 'ask_buy', 'low', 'high', 'created_at')
        ->where('type', $type)
        ->whereDate('created_at', '<=', $endDate)
        ->whereDate('created_at', '>', Carbon::parse($endDate)->subDays($days))
        ->orderBy('created_at', 'desc')
        ->get();
     } else {
        $historicalData = LiveRateData::select('type', 'ask_buy', 'created_at')
        ->whereDate('created_at', '<=', $endDate)
        ->whereDate('created_at', '>', Carbon::parse($endDate)->subDays($days))
        ->orderBy('created_at', 'desc')
        ->get();
     }

        return response()->json($historicalData);
    }

    
    // public function getHourlyLiveRateData(Request $request)
    // {
    //     $tenDaysAgo = Carbon::now()->subDays(10);
    //     $liveRateHourlyData = LiveDataHour::whereDate('created_at', '>=', $tenDaysAgo)->get();

    //     return response()->json($liveRateHourlyData);
    // }
    public function getHourlyLiveRateData(Request $request)
{
    $tenDaysAgo = Carbon::now()->subDays(10);
    $liveRateHourlyData = LiveDataHour::whereDate('created_at', '>=', $tenDaysAgo)->get();

    $groupedData = [];

    foreach ($liveRateHourlyData as $data) {
        $type = $data->type;

        // Check if the type is already in the grouped data array
        if (!isset($groupedData[$type])) {
            $groupedData[$type] = [];
        }

        // Add the data to the appropriate type
        $groupedData[$type][] = $data;
    }

    return response()->json($groupedData);
}

    // for saifur vai 
    public function showBroadcastDataCrystal(Request $request)
    {
        $response = Http::get('http://bcast.apanjewellery.com:7767/VOTSBroadcastStreaming/Services/xml/GetLiveRateByTemplateID/apan', [
            '_' => time()
        ]);

        $jsonData = $response->body(); // Get the plain text data
        $rows = explode("\n", $jsonData);
        $result = [];

        if ($rows !== null && count($rows) > 0){
            $firstObject = $rows[0];

            $elements = explode("\t", trim($firstObject));

            $jsonData = [
                'id' => $elements[0],
                'type' => $elements[1],
                'bid_sell' => $elements[2],
                'ask_buy' => $elements[3],
                'high' => $elements[4],
                'low' => $elements[5]
            ];

            $jsonString = json_encode($jsonData);

            $goldOz = json_decode($jsonString, true);

            $goldOzId = $goldOz['id'];
            $goldOztype = $goldOz['type'];
            $goldOzbid_sell = $goldOz['bid_sell'];
            $goldOzask_buy = $goldOz['ask_buy'];
            $goldOzhigh = $goldOz['high'];
            $goldOzlow = $goldOz['low'];

            $secondObject = $rows[1];

            $goldelements = explode("\t", trim($secondObject));

            $goldjsonData = [
                'id' => $goldelements[0],
                'type' => $goldelements[1],
                'bid_sell' => $goldelements[2],
                'ask_buy' => $goldelements[3],
                'high' => $goldelements[4],
                'low' => $goldelements[5]
            ];

            $goldjsonString = json_encode($goldjsonData);

            $gold = json_decode($goldjsonString, true);

            $goldId = $gold['id'];
            $goldtype = $gold['type'];
            $goldbid_sell = $gold['bid_sell'];
            $goldask_buy = $gold['ask_buy'];
            $goldhigh = $gold['high'];
            $goldlow = $gold['low'];

            $goldOztoTTB = 13.7639;
            $mes24K999 = 116.64*0.999;
            $mes24k995 = 116.64*0.995;
            $mes22k92 = 0.92;
            $kiloBar = 1000;

            $TTBid = "7524";
            $TTBtype = "TEN TOLA BAR";
            $TTBbid_sell = sprintf("%0.2f",($goldOzbid_sell * $goldOztoTTB));
            $TTBask_buy = sprintf("%0.2f",($goldOzask_buy * $goldOztoTTB));
            $TTBhigh = sprintf("%0.2f",($goldOzhigh * $goldOztoTTB));
            $TTBlow = sprintf("%0.2f",($goldOzlow * $goldOztoTTB));

            $tenTolaBar = [
                'id' => $TTBid,
                'type' => $TTBtype,
                'bid_sell' => $TTBbid_sell,
                'ask_buy' => $TTBask_buy,
                'high' => $TTBhigh,
                'low' => $TTBlow,
            ];


            $Gold999id = "7526";
            $Gold999type = "GOLD 9999";
            $Gold999bid_sell = sprintf("%0.2f",($TTBbid_sell / $mes24K999));
            $Gold999ask_buy = sprintf("%0.2f",($TTBask_buy / $mes24K999));
            $Gold999high = sprintf("%0.2f",($TTBhigh / $mes24K999));
            $Gold999low = sprintf("%0.2f",($TTBlow / $mes24K999));

            $gold999 = [
                'id' => $Gold999id,
                'type' => $Gold999type,
                'bid_sell' => $Gold999bid_sell,
                'ask_buy' => $Gold999ask_buy,
                'high' => $Gold999high,
                'low' => $Gold999low,
            ];

            $Gold92id = "8558";
            $Gold92type = "GOLD PURE 92";
            $Gold92bid_sell = sprintf("%0.2f",($Gold999bid_sell * $mes22k92));
            $Gold92ask_buy = sprintf("%0.2f",($Gold999ask_buy * $mes22k92));
            $Gold92high = sprintf("%0.2f",($Gold999high * $mes22k92));
            $Gold92low = sprintf("%0.2f",($Gold999low * $mes22k92));

            $gold92 = [
                'id' => $Gold92id,
                'type' => $Gold92type,
                'bid_sell' => $Gold92bid_sell,
                'ask_buy' => $Gold92ask_buy,
                'high' => $Gold92high,
                'low' => $Gold92low,
            ];

            $KiloBar9999id = "7523";
            $KiloBar9999type = "KILO BAR 9999";
            $KiloBar9999bid_sell = sprintf("%0.2f",((($TTBbid_sell / $mes24K999) * $kiloBar)));
            $KiloBar9999ask_buy = sprintf("%0.2f",((($TTBask_buy / $mes24K999) * $kiloBar)));
            $KiloBar9999high = sprintf("%0.2f",((($TTBhigh / $mes24K999)* $kiloBar)));
            $KiloBar9999low = sprintf("%0.2f",((($TTBlow / $mes24K999)* $kiloBar)));

            $kiloBar9999 = [
                'id' => $KiloBar9999id,
                'type' => $KiloBar9999type,
                'bid_sell' => $KiloBar9999bid_sell,
                'ask_buy' => $KiloBar9999ask_buy,
                'high' => $KiloBar9999high,
                'low' => $KiloBar9999low,
            ];

            $KiloBar995id = "7522";
            $KiloBar995type = "KILO BAR 995";
            $KiloBar995bid_sell = sprintf("%0.2f",((($TTBbid_sell / $mes24k995) * $kiloBar)));
            $KiloBar995ask_buy = sprintf("%0.2f",((($TTBask_buy / $mes24k995) * $kiloBar)));
            $KiloBar995high = sprintf("%0.2f",((($TTBhigh / $mes24k995)* $kiloBar)));
            $KiloBar995low = sprintf("%0.2f",((($TTBlow / $mes24k995)* $kiloBar)));

            $kiloBar995 = [
                'id' => "7522",
                'type' => "KILO BAR 995",
                'bid_sell' => sprintf("%0.2f",((($TTBbid_sell / $mes24k995) * $kiloBar))),
                'ask_buy' => sprintf("%0.2f",((($TTBask_buy / $mes24k995) * $kiloBar))),
                'high' => sprintf("%0.2f",((($TTBhigh / $mes24k995)* $kiloBar))),
                'low' => sprintf("%0.2f",((($TTBlow / $mes24k995)* $kiloBar))),
            ];

            $mergedArray = array_merge($goldOz, $gold, $gold92, $gold999, $tenTolaBar, $kiloBar995, $kiloBar9999);

            $mergedArray = [];

            $mergedArray[] = $goldOz;
            $mergedArray[] = $gold;
            $mergedArray[] = $gold92;
            $mergedArray[] = $gold999;
            $mergedArray[] = $tenTolaBar;
            $mergedArray[] = $kiloBar995;
            $mergedArray[] = $kiloBar9999;
            
        } else {
            // Handle JSON parsing error
            echo "Error parsing JSON data";
        }

    return $mergedArray;
    }
        
}
