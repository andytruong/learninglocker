<?php

use Jenssegers\Mongodb\Model as Eloquent;

class Lrs extends Eloquent
{

    const ADURO_AUTH_SERVICE = 1;
    const INTERNAL_LRS = 2;

    /**
     * Our MongoDB collection used by the model.
     *
     * @var string
     */
    protected $collection = 'lrs';

    /**
     * Validation rules for statement input
     */
    protected $rules = array(
        'title' => 'required|alpha_dash',
        'auth_cache_time' => 'numeric',
        'auth_service_url' => 'url',
        'subdomain' => 'alpha_dash'
    );

    public function validate($data)
    {
        return Validator::make($data, $this->rules);
    }

}
