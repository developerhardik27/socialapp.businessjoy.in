<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around the Expo Push API.
 *
 * - Sends in batches of 100 (Expo limit).
 * - Never throws: delivery failures are logged and reported back so the
 *   caller can react without breaking the main request.
 */
class ExpoPushService
{
    const ENDPOINT = 'https://exp.host/--/api/v2/push/send';

    /**
     * @param  array $messages  Expo message payloads [['to'=>..,'title'=>..,'body'=>..,'data'=>[..]], ...]
     * @param  callable|null $invalidTokenHandler  called with each token Expo reports as invalid
     * @return array|null  ticket data on success, null on total failure
     */
    public static function send(array $messages, callable $invalidTokenHandler = null)
    {
        $allTickets = [];

        foreach (array_chunk($messages, 100) as $chunk) {
            try {
                $response = Http::timeout(15)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->post(self::ENDPOINT, $chunk);
            } catch (\Throwable $e) {
                Log::warning('Expo push send failed: ' . $e->getMessage());
                continue;
            }

            if (!$response->ok()) {
                Log::warning('Expo push HTTP error ' . $response->status() . ': ' . $response->body());
                continue;
            }

            $tickets = $response->json('data') ?? [];

            foreach ($tickets as $i => $ticket) {
                $allTickets[] = $ticket;

                if (($ticket['status'] ?? '') === 'error'
                    && ($ticket['details']['error'] ?? '') === 'DeviceNotRegistered'
                    && $invalidTokenHandler
                    && isset($chunk[$i]['to'])) {
                    $invalidTokenHandler($chunk[$i]['to']);
                }
            }
        }

        return $allTickets;
    }
}
