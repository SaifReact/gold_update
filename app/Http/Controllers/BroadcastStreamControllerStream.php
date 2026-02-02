<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BroadcastStreamControllerStream extends Controller
{
    public function stream()
    {
        // VERY IMPORTANT
        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', 'off');
        @ini_set('implicit_flush', 1);
        ob_implicit_flush(true);

        return new StreamedResponse(function () {

            $response = Http::withOptions([
                'stream'  => true,
                'timeout' => 0,
            ])->get(
                'http://bcast.apanjewellery.com:7767/VOTSBroadcastStreaming/Services/xml/GetLiveRateByTemplateID/apan',
                ['_' => microtime(true)]
            );

            $body = $response->toPsrResponse()->getBody();

            while (! $body->eof()) {

                $chunk = $body->read(256); // small chunk

                if ($chunk !== '') {
                    echo $chunk;

                    // FORCE SEND NOW
                    echo "\n";   // helps browser/curl flush
                    flush();
                }
            }

        }, 200, [
            'Content-Type'  => 'text/plain',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma'        => 'no-cache',
            'Connection'    => 'keep-alive',
            'X-Accel-Buffering' => 'no', // NGINX magic
        ]);
    }
}
