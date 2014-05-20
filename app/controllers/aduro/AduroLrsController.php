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
    }

    /**
    * get LRS list
    *
    */
    public function index() {
        $lrsId = \Request::instance()->query('lrsId');

        $output = [];
        if ($lrsId) {
            $lrs = \Lrs::where('_id', $lrsId)->first();
            $output[] = [
                'id' => $lrs->_id,
                'title' => $lrs->title,
                'description' => $lrs->description,
                'api' => $lrs->api,
                'auth_service' => $lrs->auth_service,
                'auth_service_url' => $lrs->auth_service_url,
                'token' => $lrs->token
            ];
        } else {
            $lrss = \Lrs::get();
            foreach ($lrss as $lrs) {
                $output[] = [
                    'id' => $lrs->_id,
                    'title' => $lrs->title,
                    'description' => $lrs->description,
                    'api' => $lrs->api,
                    'auth_service' => $lrs->auth_service,
                    'auth_service_url' => $lrs->auth_service_url,
                    'token' => $lrs->token
                ];
            }
        }

        $ouput = [
            'lrs' => $output
        ];

        return \Response::json($ouput);
    }

    /**
    * Store a newly created resource in storage.
    *
    */
    public function create()
    {	
    	$input = json_decode(\Request::instance()->getContent(), TRUE);
        $validator = $this->validate($input);
        if ($validator['success'] === false) {
            return \Response::json($validator);
        }

        //creating new user
        $userName = helpers::getRandomValue();

        $user = new \User;
        $user->name = $userName;
        $user->email = $userName.'@go1.com.au';
        $user->verified = 'yes';
        $user->role = 'super';
        $user->password = \Hash::make(base_convert(uniqid('pass', true), 10, 36));
        $user->save();

        // creating new LRS
        $lrs = new \Lrs;
        $lrs->title = $input['title'];
        $lrs->description = $input['description'];
        $lrs->auth_service = $input['auth_service'];
        $lrs->auth_service_url = isset($input['auth_service_url']) ? $input['auth_service_url'] : '';
        $lrs->auth_cache_time = isset($input['auth_cache_time']) ? $input['auth_cache_time'] : '';
        $lrs->token = isset($input['token']) ? $input['token'] : '';
        $lrs->subdomain = isset($input['subdomain']) ? $input['subdomain'] : '';
        $lrs->api = ['basic_key' => helpers::getRandomValue(),
            'basic_secret' => helpers::getRandomValue()
        ];
        $lrs->owner = ['_id' => $user->_id];
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

            try {
                \Event::fire('user.create_lrs', array('user' => $user));
                \Event::fire('lrs.create', array('lrs' => $lrs));
            } catch (Exception $e) {
                $ouput = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }

            return \Response::json($ouput);
        }

        $ouput = [
    		'success' => false,
    		'message' => 'Can\'t save lrs'
    	];
        return \Response::json($ouput);
    }

    public function validate($input)
    {
        if (!$input) {
            return [
                'success' => false,
                'message' => 'Content can\'t null'
            ];
        }

        $rules['title'] = 'required|alpha_dash|unique:lrs';
        $rules['description'] = 'required|alpha_spaces';
        $rules['auth_service'] = 'required|numeric';
        $rules['auth_cache_time'] = 'numeric';
        $rules['auth_service_url'] = 'url';
        $rules['subdomain'] = 'unique:lrs|alpha_dash';

        $validator = \Validator::make($input, $rules);
        
        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => "Error data"
            ];
        }

        return ['success' => true];
    }
}