<?php

namespace app\aduro\authentication;

use \GuzzleHttp\Client as Client;
use \GuzzleHttp\Exception\ClientException as ClientException;


class AuthenticationService implements AuthenticationInterface
{

    public function verify($key, $secret)
    {
        try {
            $lrs = \LrsHelpers::getLrsBySubdomain();

            // Get timestamp from request parameter
            $timestamp = isset($_GET['timestamp']) ? $_GET['timestamp'] : time();
            
            // cache auth service
            $cache_key = "{$key}_{$secret}_{$timestamp}";
            if (\Cache::has($cache_key)) {
                $res = \Cache::get($cache_key, '');
                $return = unserialize($res);
            }
            else {
                $client = new Client();
                $res = $client->get("{$lrs->auth_service_url}/client/validate/{$key}/{$secret}/{$timestamp}", ['query' => ['token' => $lrs->token]]);
                $minutes = !empty($lrs->auth_cache_time) ? $lrs->auth_cache_time : 15;
                $return = $res->json();
                \Cache::put($cache_key, serialize($return), $minutes);
            }
            
            if (isset($return['error']) && $return['error']) {
                return \Response::json(array(
                    'error' => true,
                    'message' => 'Unauthorized request.'), 401
                );
            }
            return;
        }
        catch (ClientException $ex) {
            return \Response::json(array(
                    'error' => true,
                    'message' => 'Unauthorized request.'), 401
            );
        }
    }

    public function verifyLrsService()
    {
        $key = \Request::getUser();
        $secret = \Request::getPassword();
        $timestamp = \Request::get('timestamp');

        try {
            // cache auth service
            $cache_key = "{$key}_{$secret}_{$timestamp}";
            if (\Cache::has($cache_key)) {
                $res = \Cache::get($cache_key, '');
                $return = unserialize($res);
            }
            else {
                $site = \Site::first();
                if (!$site->auth_token || !$site->auth_service_url) {
                    return \Response::json(array(
                            'error' => true,
                            'message' => 'Please configure auth token or auth service url in LRS Service'
                        )
                    );
                }

                $client = new Client();
                $url = "{$site->auth_service_url}/client/validate/{$key}/{$secret}/{$timestamp}";
                $urlParam = ['query' => ['token' => $site->auth_token]];
                $res = $client->get($url, $urlParam);

                $minutes = !empty($site->auth_cache_time) ? $site->auth_cache_time : 15;
                $return = $res->json();
                \Cache::put($cache_key, serialize($return), $minutes);
            }
            
            if (isset($return['error']) && $return['error']) {
                return \Response::json(array(
                        'error' => true,
                        'message' => 'Unauthorized request.'
                    )
                );
            }
            return;
        }
        catch (ClientException $ex) {
            return \Response::json(array(
                    'error' => true,
                    'message' => $ex->getMessage()
                )
            );
        }
    }

}
