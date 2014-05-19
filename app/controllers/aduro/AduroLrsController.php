<?php

namespace Controllers\Aduro;

use Locker\Repository\Lrs\LrsRepository as Lrs;
use \app\locker\helpers\Helpers as helpers;

class AduroLrsController extends \Controller
{	
	/**
    * Lrs
    */
	protected $lrs;

	/**
	* Construct
	*
	* @param Locker\Repository\Lrs\LrsRepository
	*
	*/
	public function __construct(Lrs $lrs)
    {
        $this->lrs = $lrs;
        $this->beforeFilter('auth.basic');
    }

    /**
     * Store a newly created resource in storage.
     *
     */
    public function create()
    {	
    	$input = json_decode(\Request::instance()->getContent(), TRUE);
    	if (!$input) {
    		$ouput = [
        		'success' => false,
        		'message' => 'Content can\'t null'
        	];
            return \Response::json($ouput);
    	}

        // Store lrs
        $user = \Auth::user();
        
        $lrs = new \Lrs;

        $validator = $lrs->validate($input);
        if ($validator->fails()) {
            $ouput = [
                'success' => false,
                'message' => $validator->messages()
            ];
            return \Response::json($ouput);
        }

        $lrs->title = $input['title'];
        $lrs->auth_service = $input['auth_service'];
        $lrs->auth_service_url = isset($input['auth_service_url']) ? $input['auth_service_url'] : '';
        $lrs->auth_cache_time = isset($input['auth_cache_time']) ? $input['auth_cache_time'] : '';
        $lrs->token = isset($input['token']) ? $input['token'] : '';
        $lrs->subdomain = isset($input['subdomain']) ? $input['subdomain'] : '';
        $lrs->description = $input['description'];
        $lrs->api = ['basic_key' => helpers::getRandomValue(),
            'basic_secret' => helpers::getRandomValue()
        ];
        $lrs->owner = ['_id' => \Auth::user()->_id];
        $lrs->users = [
            ['_id' => $user->_id,
                'email' => $user->email,
                'name' => $user->name,
                'role' => 'admin'
            ]
        ];

        $lrs->save() ? $result = true : $return = false;

        //fire a create lrs event if it worked and saced
        if ($result) {
            $ouput = [
        		'success' => true,
        		'new_lrs' => $lrs->_id
        	];

            \Event::fire('user.create_lrs', array('user' => $user));
            \Event::fire('lrs.create', array('lrs' => $lrs));

            return \Response::json($ouput);
        }

        $ouput = [
    		'success' => false,
    		'message' => 'Can\'t save lrs'
    	];
        return \Response::json($ouput);
    }
}