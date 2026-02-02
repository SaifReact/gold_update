<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class BroadcastStreamControllerSse extends Controller
{
    public function sse()
    {
        // absolutely required
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', 'off');
        @ini_set('implicit_flush', 1);
        ob_implicit_flush(true);

        return response()->stream(function () {

            try {
                $response = Http::withOptions([
                    'stream'  => true,
                    'timeout' => 0,
                ])->get(
                    'http://bcast.apanjewellery.com:7767/VOTSBroadcastStreaming/Services/xml/GetLiveRateByTemplateID/apan'
                );

                $body = $response->toPsrResponse()->getBody();

                while (! $body->eof()) {
                    $chunk = trim($body->read(256));

                    if ($chunk !== '') {
                        echo "data: {$chunk}\n\n";
                        flush();
                    }
                }

            } catch (\Throwable $e) {
                echo "event: error\n";
                echo "data: stream_error\n\n";
                flush();
            }

        }, 200, [
            'Content-Type'        => 'text/event-stream',
            'Cache-Control'       => 'no-cache',
            'Connection'          => 'keep-alive',
            'X-Accel-Buffering'   => 'no',
        ]);
    }
}
