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
            
            // see if the lrs exists based on _id and clientname
            $lrs_owner = \Lrs::where('_id', $lrs->_id)
                    ->where('client_name', $key)
                    ->select('owner._id')->first();
            
            // if client cant owner of lrs, return error
            if ($lrs_owner == NULL) {
                return \Response::json(array(
                        'error' => true,
                        'message' => 'Unauthorized request.'), 401
                );
            }

            // Get timestamp from request parameter
            $timestamp = !is_null(\Request::get('timestamp')) ? \Request::get('timestamp') : time();

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
        $site = \Site::first();
        if (!$site->use_auth) {// for development
            return;
        }

        if (!$site->auth_token || !$site->auth_service_url) {
            return \Response::json(array(
                    'error' => true,
                    'message' => 'Please configure auth token or auth service url in LRS Service'
                    )
            );
        }

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
