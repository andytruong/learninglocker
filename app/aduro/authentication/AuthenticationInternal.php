<?php

namespace app\aduro\authentication;

class AuthenticationInternal implements AuthenticationInterface
{

    public function verify($key, $secret)
    {
        $method = \Request::server('REQUEST_METHOD');

        if ($method !== "OPTIONS") {
            // see if the lrs exists based on key and secret
            $lrs = \Lrs::where('api.basic_key', $key)
                    ->where('api.basic_secret', $secret)
                    ->select('owner._id')->first();

            // if no id found, return error
            if ($lrs == NULL) {
                return \Response::json(array(
                        'error' => true,
                        'message' => 'Unauthorized request.'), 401
                );
            }

            // attempt login once
            if (!\Auth::onceUsingId($lrs->owner['_id'])) {
                return \Response::json(array(
                        'error' => true,
                        'message' => 'Unauthorized Request'), 401
                );
            }
        }
        return;
    }

}
