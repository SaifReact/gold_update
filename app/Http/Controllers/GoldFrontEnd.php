<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\LiveDataHour;
use App\Models\LiveRateData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;

class GoldFrontEnd extends Controller
{
    private function fetchBroadcastData()
    {
        try {
            $response = Http::get('http://bcast.apanjewellery.com:7767/VOTSBroadcastStreaming/Services/xml/GetLiveRateByTemplateID/apan', [
                '_' => time()
            ]);
            return explode("\n", $response->body());  // Return the response body split into rows
        } catch (RequestException $e) {
            Log::error('Error connecting to the server: ' . $e->getMessage());
            return null;
        }
    }

    public function showBroadcastData()
    {
        $rows = $this->fetchBroadcastData();
        
        if ($rows === null) {
            return response()->json(['error' => 'Could not fetch data from the server'], 500);
        }

        $result = [];
        foreach ($rows as $row) {
            $columns = explode("\t", trim($row));
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

        return response()->json($result);
    }

    public function showBroadcastDataCrystal(Request $request)
    {
        $rows = $this->fetchBroadcastData();

        if ($rows === null) {
            return response()->json(['error' => 'Could not fetch data from the server'], 500);
        }

        $goldOz = $this->processData($rows[0]);
        $sliverOz = $this->processData($rows[1]);
        $gold = $this->processData($rows[2]);

        $goldOztoTTB = 13.7639;
        $mes24K999 = 116.64 * 0.999;
        $mes24k995 = 116.64 * 0.995;
        $mes22k92 = 0.92;
        $kiloBar = 1000;
        $silKiloBar = 32.15;
        $aed = 3.674;

        $TTB = $this->calculateTTB($goldOz, $goldOztoTTB);
        $tenTolaBar = [
            'id' => "7524",
            'type' => "TEN TOLA BAR",
            'bid_sell' => $TTB['bid_sell'],
            'ask_buy' => $TTB['ask_buy'],
            'high' => $TTB['high'],
            'low' => $TTB['low'],
        ];

        $gold999 = $this->calculateGold999($TTB, $mes24K999);
        $gold92 = $this->calculateGold92($gold999, $mes22k92);
        $silKiloBar = $this->calculateSilKiloBar($sliverOz, $silKiloBar, $aed);
        $kiloBar9999 = $this->calculateKiloBar9999($TTB, $mes24K999, $kiloBar);
        $kiloBar995 = $this->calculateKiloBar995($TTB, $mes24k995, $kiloBar);

        $result = [
            $goldOz, $sliverOz, $gold92, $gold999, $tenTolaBar, $silKiloBar, $kiloBar995, $kiloBar9999
        ];

        return response()->json($result);
    }

    private function processData($row)
    {
        $elements = explode("\t", trim($row));
        return [
            'id' => $elements[0],
            'type' => $elements[1],
            'bid_sell' => $elements[2],
            'ask_buy' => $elements[3],
            'high' => $elements[4],
            'low' => $elements[5]
        ];
    }

    private function calculateTTB($goldOz, $goldOztoTTB)
    {
        return [
            'bid_sell' => sprintf("%0.2f", $goldOz['bid_sell'] * $goldOztoTTB),
            'ask_buy' => sprintf("%0.2f", $goldOz['ask_buy'] * $goldOztoTTB),
            'high' => sprintf("%0.2f", $goldOz['high'] * $goldOztoTTB),
            'low' => sprintf("%0.2f", $goldOz['low'] * $goldOztoTTB),
        ];
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
            'id' => "7522",
            'type' => "KILO BAR 995",
            'bid_sell' => sprintf("%0.2f", ($TTB['bid_sell'] / $mes24K995) * $kiloBar),
            'ask_buy' => sprintf("%0.2f", ($TTB['ask_buy'] / $mes24K995) * $kiloBar),
            'high' => sprintf("%0.2f", ($TTB['high'] / $mes24K995) * $kiloBar),
            'low' => sprintf("%0.2f", ($TTB['low'] / $mes24K995) * $kiloBar),
        ];
    }
}
