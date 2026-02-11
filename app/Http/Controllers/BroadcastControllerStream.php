<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BroadcastControllerStream extends Controller
{
    public function stream(Request $request)
    {
        // Streaming helpers
        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', 'off');
        @ini_set('implicit_flush', 1);
        ob_implicit_flush(true);

        $parseLineToState = function (string $fullLine, array &$state) {
            $line = trim($fullLine);
            if ($line === '') return;
            if (! preg_match('/^(\d+)\s+(.*)$/', $line, $m)) return;
            $id = $m[1];
            $rest = trim($m[2]);

            // find numeric columns and their offsets
            preg_match_all('/-?\d+\.\d+/', $rest, $numMatches, PREG_OFFSET_CAPTURE);
            $numbers = [];
            $firstNumPos = null;
            $lastNumEnd = null;
            if (! empty($numMatches[0])) {
                foreach ($numMatches[0] as $n) {
                    $numbers[] = $n[0];
                }
                $firstNumPos = $numMatches[0][0][1];
                $last = end($numMatches[0]);
                $lastNumEnd = $last[1] + strlen($last[0]);
            }

            // default name part is everything before first number (if present) or full rest
            $namePart = $firstNumPos !== null ? trim(substr($rest, 0, $firstNumPos)) : trim($rest);

            // try to detect unit/weight after numbers first
            $unitToken = null;
            if ($lastNumEnd !== null && $lastNumEnd < strlen($rest)) {
                $after = trim(substr($rest, $lastNumEnd));
                if ($after !== '') {
                    $unitToken = $after;
                    // strip from name if it was accidentally included
                    $namePart = trim($namePart);
                }
            }

            // if no unit after numbers, check end of the namePart (covers 'GOLD OZ' before numbers)
            if ($unitToken === null && $namePart !== '') {
                // capture trailing token(s) like 'OZ', 'KILO BAR', '1 GM', 'TTB'
                    if (preg_match('/((?:\d+\s*(?:GM|KG|G|OZ))|KILO\s*BAR|TEN\s*TOLA\s*BAR|TTB|OZ|GM|KG)$/i', $namePart, $uMatch)) {
                            // match only numeric+unit (e.g. '1 GM') or known unit words
                            // do NOT match pure numeric codes like '995' so they remain part of the type
                            $unitToken = trim($uMatch[0]);
                            // keep the original namePart (do not strip token) so codes like 'KILO BAR 995' remain intact
                        }
            }

            // normalize unit token to desired weight strings
            $weight = null;
            if ($unitToken !== null) {
                $ut = strtoupper(trim($unitToken));
                if (preg_match('/\bOZ\b/i', $ut)) {
                    $weight = 'OZ';
                } elseif (preg_match('/\bGM\b/i', $ut)) {
                    // numbers like '1 GM' may be present in token; preserve number when available
                    if (preg_match('/(\d+)\s*GM/i', $unitToken, $mnum)) {
                        $weight = trim($mnum[1] . ' GM');
                    } else {
                        $weight = '1 GM';
                    }
                } elseif (preg_match('/\bKG\b/i', $ut) || preg_match('/KILO/i', $ut)) {
                    if (preg_match('/(\d+)\s*KG/i', $unitToken, $mnum)) {
                        $weight = trim($mnum[1] . ' KG');
                    } else {
                        $weight = '1 KG';
                    }
                } elseif (preg_match('/TTB|TEN\s*TOLA/i', $ut)) {
                    $weight = 'TTB';
                } else {
                    // fallback: use token as-is (trimmed)
                    $weight = trim($unitToken);
                }
            }

            $type = preg_replace('/\s+/', ' ', trim($namePart));
            if ($type === '') {
                // fallback: if name empty, try to build from rest excluding numeric columns and unit
                $type = preg_replace('/\s+/', ' ', trim(preg_replace('/-?\d+\.\d+/', ' ', $rest)));
            }

            $state[$id] = [
                'id' => (string) $id,
                'type' => $type,
                'weight' => $weight,
                'bid_sell' => isset($numbers[0]) ? $numbers[0] : null,
                'ask_buy'  => isset($numbers[1]) ? $numbers[1] : null,
                'high'     => isset($numbers[2]) ? $numbers[2] : null,
                'low'      => isset($numbers[3]) ? $numbers[3] : null,
            ];
        };

        // One-shot JSON mode (supports optional ?id=7522 to return single record)
        if ($request->boolean('once') || $request->get('once') === '1' || $request->get('mode') === 'once') {
            $response = Http::withOptions(['timeout' => 10])->get(
                'http://bcast.apanjewellery.com:7767/VOTSBroadcastStreaming/Services/xml/GetLiveRateByTemplateID/apan',
                ['_' => microtime(true)]
            );

            $text = (string) $response->body();
            $lines = preg_split('/\r?\n/', $text);
            $state = [];
            $pending = null;
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') continue;
                if (preg_match('/^\d+\s+/', $line)) {
                    if ($pending !== null) {
                        $parseLineToState($pending, $state);
                    }
                    $pending = $line;
                } else {
                    if ($pending === null) {
                        $pending = $line;
                    } else {
                        $pending .= ' ' . $line;
                    }
                }
            }
            if ($pending !== null) {
                $parseLineToState($pending, $state);
            }

            $results = array_values($state);
            if ($request->filled('id')) {
                $id = (string) $request->get('id');
                $filtered = array_values(array_filter($results, function ($r) use ($id) {
                    return isset($r['id']) && $r['id'] === $id;
                }));
                return response()->json($filtered);
            }

            return response()->json($results);
        }

        // Streaming response: emit periodic JSON snapshots (text/plain for browser flush)
        return new StreamedResponse(function () use ($parseLineToState, $request) {
            $response = Http::withOptions([
                'stream'  => true,
                'timeout' => 0,
            ])->get(
                'http://bcast.apanjewellery.com:7767/VOTSBroadcastStreaming/Services/xml/GetLiveRateByTemplateID/apan',
                ['_' => microtime(true)]
            );

            $body = $response->toPsrResponse()->getBody();

            $buffer = '';
            $pending = null;
            $state = [];
            $lastDataTime = 0; // track last time we parsed a record
            $debug = true;

            while (! $body->eof()) {
                $chunk = $body->read(256);
                if ($chunk === '') {
                    usleep(10000);
                    continue;
                }

                if ($debug) {
                    Log::info('STREAM_FIRST_CHUNK: ' . substr($chunk, 0, 2000));
                    $debug = false;
                }

                $buffer .= $chunk;

                while (($newlinePos = strpos($buffer, "\n")) !== false) {
                    $line = substr($buffer, 0, $newlinePos);
                    $buffer = substr($buffer, $newlinePos + 1);
                    $line = trim($line);
                    if ($line === '') continue;

                    if (preg_match('/^\d+\s+/', $line)) {
                        if ($pending !== null) {
                            $parseLineToState($pending, $state);
                            $lastDataTime = microtime(true);
                        }
                        $pending = $line;
                    } else {
                        if ($pending === null) {
                            $pending = $line;
                        } else {
                            $pending .= ' ' . $line;
                        }
                    }
                }
                // if we've parsed at least one record and there's been a quiet period, stop and emit once
                if ($lastDataTime > 0 && (microtime(true) - $lastDataTime) >= 1.0 && ! empty($state)) {
                    break;
                }
            }

            // final flush
            if ($pending !== null) {
                $parseLineToState($pending, $state);
                $pending = null;
            }

            $results = array_values($state);
            if ($request->filled('id')) {
                $id = (string) $request->get('id');
                $results = array_values(array_filter($results, function ($r) use ($id) {
                    return isset($r['id']) && $r['id'] === $id;
                }));
            }

            echo json_encode($results);
            echo "\n";
            flush();

        }, 200, [
            'Content-Type'  => 'application/json; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma'        => 'no-cache',
            'Connection'    => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
