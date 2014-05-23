<?php

namespace Controllers\Aduro;

use Locker\Repository\Lrs\LrsRepository as Lrs;
use app\locker\helpers\Helpers as helpers;

class AduroLrsController extends \Controller
{

    /**
     * Lrs
     */
    protected $lrs;

    /**
     * Lrs rules
     */
    protected $rules = [
        'title' => 'required|alpha_dash|unique:lrs',
        'description' => 'required|alpha_spaces',
        'auth_service' => 'required|numeric',
        'auth_cache_time' => 'numeric',
        'auth_service_url' => 'url',
        'subdomain' => 'unique:lrs|alpha_dash'
    ];

    /**
     * Construct
     *
     * @param Locker\Repository\Lrs\LrsRepository
     */
    public function __construct(Lrs $lrs)
    {
        $this->lrs = $lrs;
        $this->beforeFilter('aduro.lrs');
    }

    /**
     * Get LRS list
     */
    public function index()
    {
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

        $ouput = [
            'lrs' => $output
        ];

        return \Response::json($ouput);
    }

    /**
     * Get LRS by id
     */
    public function show($id)
    {
        $lrs = \Lrs::find($id);

        if (!$lrs) {
            $output = [
                'success' => false,
                'message' => 'Invalid id'
            ];

            return \Response::json($output);
        }

        $output = [
            'id' => $lrs->_id,
            'title' => $lrs->title,
            'description' => $lrs->description,
            'api' => $lrs->api,
            'auth_service' => $lrs->auth_service,
            'auth_service_url' => $lrs->auth_service_url,
            'token' => $lrs->token
        ];

        $ouput = [
            'lrs' => $output
        ];

        return \Response::json($ouput);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        $input = json_decode(\Request::instance()->getContent(), TRUE);
        $validator = $this->validate($input);
        if ($validator['success'] === false) {
            return \Response::json($validator);
        }

        // creating new user
        $userName = helpers::getRandomValue();

        $user = new \User;
        $user->name = $userName;
        $user->email = $userName . '@go1.com.au';
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

        // fire a create lrs event if it worked and saced
        if ($lrs->save()) {
            $ouput = [
                'success' => true,
                'new_lrs' => $lrs->_id
            ];

            try {
                \Event::fire('user.create_lrs', ['user' => $user]);
                \Event::fire('lrs.create', ['lrs' => $lrs]);
            }
            catch (Exception $e) {
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

    /**
     * Update LRS by id.
     */
    public function update($id)
    {
        $lrs = \Lrs::find($id);

        if (!$lrs) {
            $output = [
                'success' => false,
                'message' => 'Invalid id'
            ];

            return \Response::json($output);
        }

        $input = json_decode(\Request::instance()->getContent(), TRUE);

        $rules = [];
        foreach ($input as $key => $value) {
            $lrs->{$key} = $value;
            if (isset($this->rules[$key])) {
                $rules[$key] = $this->rules[$key];
            }
        }

        $validator = $this->validate($input, $rules);
        if ($validator['success'] === false) {
            return \Response::json($validator);
        }

        $lrs->save();

        $ouput = [
            'success' => true
        ];
        return \Response::json($ouput);
    }

    /**
     * Delete LRS by id.
     */
    public function destroy($id)
    {
        $lrs = \Lrs::find($id);

        if (!$lrs) {
            $output = [
                'success' => false,
                'message' => 'Invalid id'
            ];

            return \Response::json($output);
        }

        $lrs->delete();

        $output = [
            'success' => true
        ];

        return \Response::json($output);
    }

    /**
     * Follow http://laravel.com/docs/controllers#resource-controllers but not work
     */
    public function missingMethod($parameters = [])
    {
        $output = [
            'success' => false,
            'message' => 'Missing Methods'
        ];

        return \Response::json($output);
    }

    /**
     * Validate lrs data
     *
     * @param type $input
     * @param type $rules
     * @return type
     */
    public function validate($input, $rules = [])
    {
        if (!$input) {
            return [
                'success' => false,
                'message' => 'Content can\'t null'
            ];
        }

        if (empty($rules)) {
            $rules = $this->rules;
        }
        $validator = \Validator::make($input, $rules);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->messages()->all()
            ];
        }

        return ['success' => true];
    }

}
