<?php

namespace App\Http\Controllers;

class TestSseController extends Controller
{
    public function test()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', 'off');
        @ini_set('implicit_flush', 1);
        ob_implicit_flush(true);

        return response()->stream(function () {

            $i = 0;

            while (true) {
                echo "data: Time = " . microtime(true) . "\n\n";
                flush();
                usleep(100000); // 100 ms
                $i++;
            }

        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
