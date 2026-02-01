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
    // public function showBroadcastDataCrystal(Request $request)
    // {
    //     $response = Http::get('http://bcast.apanjewellery.com:7767/VOTSBroadcastStreaming/Services/xml/GetLiveRateByTemplateID/apan', [
    //         '_' => time()
    //     ]);

    //     $jsonData = $response->body(); // Get the plain text data
    //     $rows = explode("\n", $jsonData);
    //     $result = [];

    //     if ($rows !== null && count($rows) > 0){
    //         $firstObject = $rows[0];

    //         $elements = explode("\t", trim($firstObject));

    //         $jsonData = [
    //             'id' => $elements[0],
    //             'type' => $elements[1],
    //             'bid_sell' => $elements[2],
    //             'ask_buy' => $elements[3],
    //             'high' => $elements[4],
    //             'low' => $elements[5]
    //         ];

    //         $jsonString = json_encode($jsonData);            

    //         $goldOz = json_decode($jsonString, true);

    //         $goldOzId = $goldOz['id'];
    //         $goldOztype = $goldOz['type'];
    //         $goldOzbid_sell = $goldOz['bid_sell'];
    //         $goldOzask_buy = $goldOz['ask_buy'];
    //         $goldOzhigh = $goldOz['high'];
    //         $goldOzlow = $goldOz['low'];

    //         $secondObject = $rows[1];

    //         $sliverelements = explode("\t", trim($secondObject));

    //         $sliverjsonData = [
    //             'id' => $sliverelements[0],
    //             'type' => $sliverelements[1],
    //             'bid_sell' => $sliverelements[2],
    //             'ask_buy' => $sliverelements[3],
    //             'high' => $sliverelements[4],
    //             'low' => $sliverelements[5]
    //         ];

    //         $sliverjsonString = json_encode($sliverjsonData);

    //         $sliverOz = json_decode($sliverjsonString, true);

    //         $sliverId = $sliverOz['id'];
    //         $slivertype = $sliverOz['type'];
    //         $sliverbid_sell = $sliverOz['bid_sell'];
    //         $sliverask_buy = $sliverOz['ask_buy'];  
    //         $sliverhigh = $sliverOz['high'];
    //         $sliverlow = $sliverOz['low'];

    //         $thirdObject = $rows[2];

    //         $goldelements = explode("\t", trim($thirdObject));

    //         $goldjsonData = [
    //             'id' => $goldelements[0],
    //             'type' => $goldelements[1],
    //             'bid_sell' => $goldelements[2],
    //             'ask_buy' => $goldelements[3],
    //             'high' => $goldelements[4],
    //             'low' => $goldelements[5]
    //         ];

    //         $goldjsonString = json_encode($goldjsonData);

    //         $gold = json_decode($goldjsonString, true);

    //         $goldId = $gold['id'];
    //         $goldtype = $gold['type'];
    //         $goldbid_sell = $gold['bid_sell'];
    //         $goldask_buy = $gold['ask_buy'];
    //         $goldhigh = $gold['high'];
    //         $goldlow = $gold['low'];

    //         $goldOztoTTB = 13.7639;
    //         $mes24K999 = 116.64*0.999;
    //         $mes24k995 = 116.64*0.995;
    //         $mes22k92 = 0.92;
    //         $kiloBar = 1000;
    //         $silKiloBar = 32.1507;
    //         $aed = 3.674;


    //         $TTBid = "7524";
    //         $TTBtype = "TEN TOLA BAR";
    //         $TTBbid_sell = sprintf("%0.2f",($goldOzbid_sell * $goldOztoTTB));
    //         $TTBask_buy = sprintf("%0.2f",($goldOzask_buy * $goldOztoTTB));
    //         $TTBhigh = sprintf("%0.2f",($goldOzhigh * $goldOztoTTB));
    //         $TTBlow = sprintf("%0.2f",($goldOzlow * $goldOztoTTB));

    //         $tenTolaBar = [
    //             'id' => $TTBid,
    //             'type' => $TTBtype,
    //             'bid_sell' => $TTBbid_sell,
    //             'ask_buy' => $TTBask_buy,
    //             'high' => $TTBhigh,
    //             'low' => $TTBlow,
    //         ];

            

    //         $Gold999id = "7526";
    //         $Gold999type = "GOLD 9999";
    //         $Gold999bid_sell = sprintf("%0.2f",($TTBbid_sell / $mes24K999));
    //         $Gold999ask_buy = sprintf("%0.2f",($TTBask_buy / $mes24K999));
    //         $Gold999high = sprintf("%0.2f",($TTBhigh / $mes24K999));
    //         $Gold999low = sprintf("%0.2f",($TTBlow / $mes24K999));

    //         $gold999 = [
    //             'id' => $Gold999id,
    //             'type' => $Gold999type,
    //             'bid_sell' => $Gold999bid_sell,
    //             'ask_buy' => $Gold999ask_buy,
    //             'high' => $Gold999high,
    //             'low' => $Gold999low,
    //         ];

    //         // Silver Kilo Bar Calculation

    //         $SilKiloBarid = "7525";
    //         $SilKiloBartype = "SILVER KILO BAR";
    //         $SilKiloBarbid_sell = sprintf("%0.2f",($sliverbid_sell * $silKiloBar * $aed));
    //         $SilKiloBarask_buy = sprintf("%0.2f",($sliverask_buy * $silKiloBar * $aed));
    //         $SilKiloBarhigh = sprintf("%0.2f",($sliverhigh * $silKiloBar * $aed));
    //         $SilKiloBarlow = sprintf("%0.2f",($sliverlow * $silKiloBar * $aed));

    //         $silKiloBar = [
    //             'id' => $SilKiloBarid,
    //             'type' => $SilKiloBartype,
    //             'bid_sell' => $SilKiloBarbid_sell,
    //             'ask_buy' => $SilKiloBarask_buy,
    //             'high' => $SilKiloBarhigh,
    //             'low' => $SilKiloBarlow,
    //         ];

    //         $Gold92id = "8558";
    //         $Gold92type = "GOLD PURE 92";
    //         $Gold92bid_sell = sprintf("%0.2f",($Gold999bid_sell * $mes22k92));
    //         $Gold92ask_buy = sprintf("%0.2f",($Gold999ask_buy * $mes22k92));
    //         $Gold92high = sprintf("%0.2f",($Gold999high * $mes22k92));
    //         $Gold92low = sprintf("%0.2f",($Gold999low * $mes22k92));

    //         $gold92 = [
    //             'id' => $Gold92id,
    //             'type' => $Gold92type,
    //             'bid_sell' => $Gold92bid_sell,
    //             'ask_buy' => $Gold92ask_buy,
    //             'high' => $Gold92high,
    //             'low' => $Gold92low,
    //         ];

    //         $KiloBar9999id = "7523";
    //         $KiloBar9999type = "KILO BAR 9999";
    //         $KiloBar9999bid_sell = sprintf("%0.2f",((($TTBbid_sell / $mes24K999) * $kiloBar)));
    //         $KiloBar9999ask_buy = sprintf("%0.2f",((($TTBask_buy / $mes24K999) * $kiloBar)));
    //         $KiloBar9999high = sprintf("%0.2f",((($TTBhigh / $mes24K999)* $kiloBar)));
    //         $KiloBar9999low = sprintf("%0.2f",((($TTBlow / $mes24K999)* $kiloBar)));

    //         $kiloBar9999 = [
    //             'id' => $KiloBar9999id,
    //             'type' => $KiloBar9999type,
    //             'bid_sell' => $KiloBar9999bid_sell,
    //             'ask_buy' => $KiloBar9999ask_buy,
    //             'high' => $KiloBar9999high,
    //             'low' => $KiloBar9999low,
    //         ];

    //         $KiloBar995id = "7522";
    //         $KiloBar995type = "KILO BAR 995";
    //         $KiloBar995bid_sell = sprintf("%0.2f",((($TTBbid_sell / $mes24k995) * $kiloBar)));
    //         $KiloBar995ask_buy = sprintf("%0.2f",((($TTBask_buy / $mes24k995) * $kiloBar)));
    //         $KiloBar995high = sprintf("%0.2f",((($TTBhigh / $mes24k995)* $kiloBar)));
    //         $KiloBar995low = sprintf("%0.2f",((($TTBlow / $mes24k995)* $kiloBar)));

    //         $kiloBar995 = [
    //             'id' => "7522",
    //             'type' => "KILO BAR 995",
    //             'bid_sell' => sprintf("%0.2f",((($TTBbid_sell / $mes24k995) * $kiloBar))),
    //             'ask_buy' => sprintf("%0.2f",((($TTBask_buy / $mes24k995) * $kiloBar))),
    //             'high' => sprintf("%0.2f",((($TTBhigh / $mes24k995)* $kiloBar))),
    //             'low' => sprintf("%0.2f",((($TTBlow / $mes24k995)* $kiloBar))),
    //         ];

    //         $mergedArray = array_merge($goldOz, $sliverOz, $gold92, $gold999, $tenTolaBar, $silKiloBar, $kiloBar995, $kiloBar9999);

    //         $mergedArray = [];

    //         $mergedArray[] = $goldOz;
    //         $mergedArray[] = $sliverOz;
    //         $mergedArray[] = $gold92;
    //         $mergedArray[] = $gold999;
    //         $mergedArray[] = $tenTolaBar;
    //         $mergedArray[] = $silKiloBar;
    //         $mergedArray[] = $kiloBar995;
    //         $mergedArray[] = $kiloBar9999;
            
    //     } else {
    //         // Handle JSON parsing error
    //         echo "Error parsing JSON data";
    //     }

    // return $mergedArray;
    // }

    public function showBroadcastDataCrystal(Request $request)
{
    // Fetch data from the external API
    $response = Http::get('http://bcast.apanjewellery.com:7767/VOTSBroadcastStreaming/Services/xml/GetLiveRateByTemplateID/apan', [
        '_' => time()
    ]);

    $jsonData = $response->body(); // Get the plain text data
    $rows = explode("\n", $jsonData);
    $result = [];

    if ($rows !== null && count($rows) > 0) {
        // Define a function to simplify data processing
        $processData = function ($row) {
            $elements = explode("\t", trim($row));
            return [
                'id' => $elements[0],
                'type' => $elements[1],
                'bid_sell' => $elements[2],
                'ask_buy' => $elements[3],
                'high' => $elements[4],
                'low' => $elements[5]
            ];
        };

        // Process each row
        $goldOz = $processData($rows[0]);
        $sliverOz = $processData($rows[1]);
        $gold = $processData($rows[2]);

        // Define constants for conversion
        $goldOztoTTB = 13.7639;
        $mes24K999 = 116.64 * 0.999;
        $mes24k995 = 116.64 * 0.995;
        $mes22k92 = 0.92;
        $kiloBar = 1000;
        $silKiloBar = 32.1507;
        $aed = 3.674;

        // Calculate TTB
        $calculateTTB = function ($bid_sell, $ask_buy, $high, $low) use ($goldOztoTTB) {
            return [
                'bid_sell' => sprintf("%0.2f", $bid_sell * $goldOztoTTB),
                'ask_buy' => sprintf("%0.2f", $ask_buy * $goldOztoTTB),
                'high' => sprintf("%0.2f", $high * $goldOztoTTB),
                'low' => sprintf("%0.2f", $low * $goldOztoTTB),
            ];
        };

        $TTB = $calculateTTB($goldOz['bid_sell'], $goldOz['ask_buy'], $goldOz['high'], $goldOz['low']);
        $tenTolaBar = [
            'id' => "7524",
            'type' => "TEN TOLA BAR",
            'bid_sell' => $TTB['bid_sell'],
            'ask_buy' => $TTB['ask_buy'],
            'high' => $TTB['high'],
            'low' => $TTB['low'],
        ];

        // Other Calculations
        $gold999 = $this->calculateGold999($TTB, $mes24K999);
        $gold92 = $this->calculateGold92($gold999, $mes22k92);
        $silKiloBar = $this->calculateSilKiloBar($sliverOz, $silKiloBar, $aed);
        $kiloBar9999 = $this->calculateKiloBar9999($TTB, $mes24K999, $kiloBar);  // Renamed this method
        $kiloBar995 = $this->calculateKiloBar995($TTB, $mes24k995, $kiloBar);  // Renamed this method

        // Merge results
        $result = [
            $goldOz, $sliverOz, $gold92, $gold999, $tenTolaBar, $silKiloBar, $kiloBar995, $kiloBar9999
        ];
    } else {
        echo "Error parsing JSON data";
    }

    return $result;
}

private function calculateGold999($TTB, $mes24K999)
{
    return [
        'id' => "7526",
        'type' => "GOLD 9999",
        'bid_sell' => sprintf("%0.2f", $TTB['bid_sell'] / $mes24K999),
        'ask_buy' => sprintf("%0.2f", $TTB['ask_buy'] / $mes24K999),
        'high' => sprintf("%0.2f", $TTB['high'] / $mes24K999),
        'low' => sprintf("%0.2f", $TTB['low'] / $mes24K999),
    ];
}

private function calculateGold92($gold999, $mes22k92)
{
    return [
        'id' => "8558",
        'type' => "GOLD PURE 92",
        'bid_sell' => sprintf("%0.2f", $gold999['bid_sell'] * $mes22k92),
        'ask_buy' => sprintf("%0.2f", $gold999['ask_buy'] * $mes22k92),
        'high' => sprintf("%0.2f", $gold999['high'] * $mes22k92),
        'low' => sprintf("%0.2f", $gold999['low'] * $mes22k92),
    ];
}

private function calculateSilKiloBar($sliverOz, $silKiloBar, $aed)
{
    return [
        'id' => "7525",
        'type' => "SILVER KILO BAR",
        'bid_sell' => sprintf("%0.2f", $sliverOz['bid_sell'] * $silKiloBar * $aed),
        'ask_buy' => sprintf("%0.2f", $sliverOz['ask_buy'] * $silKiloBar * $aed),
        'high' => sprintf("%0.2f", $sliverOz['high'] * $silKiloBar * $aed),
        'low' => sprintf("%0.2f", $sliverOz['low'] * $silKiloBar * $aed),
    ];
}

private function calculateKiloBar9999($TTB, $mes24K999, $kiloBar)
{
    return [
        'id' => "7523",
        'type' => "KILO BAR 9999",
        'bid_sell' => sprintf("%0.2f", ($TTB['bid_sell'] / $mes24K999) * $kiloBar),
        'ask_buy' => sprintf("%0.2f", ($TTB['ask_buy'] / $mes24K999) * $kiloBar),
        'high' => sprintf("%0.2f", ($TTB['high'] / $mes24K999) * $kiloBar),
        'low' => sprintf("%0.2f", ($TTB['low'] / $mes24K999) * $kiloBar),
    ];
}

private function calculateKiloBar995($TTB, $mes24K995, $kiloBar)
{
    return [
        'id' => "7522",  // Adjust the ID if needed
        'type' => "KILO BAR 995",
        'bid_sell' => sprintf("%0.2f", ($TTB['bid_sell'] / $mes24K995) * $kiloBar),
        'ask_buy' => sprintf("%0.2f", ($TTB['ask_buy'] / $mes24K995) * $kiloBar),
        'high' => sprintf("%0.2f", ($TTB['high'] / $mes24K995) * $kiloBar),
        'low' => sprintf("%0.2f", ($TTB['low'] / $mes24K995) * $kiloBar),
    ];
}        
}
