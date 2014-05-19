<?php

namespace app\aduro\authentication;

class AuthenticationService implements AuthenticationInterface
{

    public function verify($key, $secret)
    {
        try {
            $lrs = \LrsHelpers::getLrsBySubdomain();

            // Get timestamp from request parameter
            $timestamp = isset($_GET['timestamp']) ? $_GET['timestamp'] : time();

            // cache auth service
            $cache_key = "{$key}_{$secret}";
            if (\Cache::has($key)) {
                $res = \Cache::get($cache_key, '');
                $res = unserialize($res);
            }
            else {
                $client = new \GuzzleHttp\Client();
                $res = $client->get("{$lrs->auth_service_url}/client/validate/{$key}/{$secret}/{$timestamp}", ['query' => ['token' => $lrs->token]]);
                $minutes = !empty($lrs->auth_cache_time) ? $lrs->auth_cache_time : 15;
                \Cache::put($cache_key, serialize($res), $minutes);
            }
            $res->json();
            return;
        }
        catch (\GuzzleHttp\Exception\ClientException $ex) {
            return \Response::json(array(
                    'error' => true,
                    'message' => 'Unauthorized request.'), 401
            );
        }
    }

}
