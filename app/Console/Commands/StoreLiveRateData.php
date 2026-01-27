<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\LiveRateData; // Assuming your model is named LiveRateData

class StoreLiveRateData extends Command
{
    protected $signature = 'data:store';
    protected $description = 'Fetch and store live rate data';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
            $response = Http::get('http://bcast.apanjewellery.com:7767/VOTSBroadcastStreaming/Services/xml/GetLiveRateByTemplateID/apan', [
                '_' => time(), // Adding a timestamp as a cache buster
            ]);
            if ($response->ok()) {
            $data = $response->body(); // Get the plain text data
            $rows = explode("\n", $data); // Split data into rows

            foreach ($rows as $row) {
                $columns = explode("\t", trim($row)); // Split row into columns
                LiveRateData::create([
                    'type' => $columns[1],
                    'bid_sell' => $columns[2],
                    'ask_buy' => $columns[3],
                    'low' => $columns[4],
                    'high' => $columns[5]
                ]);
            }

            $this->info('Data stored successfully.');
        } else {
            $this->error('API request failed.');
        }
    }
}
