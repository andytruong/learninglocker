<?php

namespace Locker\Repository\Lrs;

use Lrs;

//use Illuminate\Database\Eloquent\Model;

class EloquentLrsRepository implements LrsRepository
{

    /**
     * @var $lrs
     */
    protected $lrs;

    /**
     * Construct
     *
     * @param $lrs
     */
    public function __construct(Lrs $lrs)
    {
        $this->lrs = $lrs;
    }

    public function all()
    {
        if (\Auth::user()->role == 'super') {
            return $this->lrs->all();
        }
        else {
            return $this->lrs->where('users._id', \Auth::user()->_id)->remember(10)->get();
        }
    }

    public function find($id)
    {
        return $this->lrs->find($id);
    }

    public function validate($data)
    {
        $lrs = new Lrs;

        return $lrs->validate($data);
    }

    public function create($input)
    {
        $user = \Auth::user();
        $lrs = new Lrs;

        $validator = $lrs->validate($input);
        if ($validator->fails()) {
            return \Redirect::back()->withErrors($validator->messages());
        }

        $lrs->title = $input['title'];
        $lrs->auth_service = $input['auth_service'];
        $lrs->auth_service_url = isset($input['auth_service_url']) ? $input['auth_service_url'] : '';
        $lrs->auth_cache_time = isset($input['auth_cache_time']) ? $input['auth_cache_time'] : '';
        $lrs->token = isset($input['token']) ? $input['token'] : '';
        $lrs->subdomain = isset($input['subdomain']) ? $input['subdomain'] : '';
        $lrs->description = $input['description'];
        $lrs->api = array('basic_key' => \app\locker\helpers\Helpers::getRandomValue(),
            'basic_secret' => \app\locker\helpers\Helpers::getRandomValue());
        $lrs->owner = array('_id' => \Auth::user()->_id);
        $lrs->users = array(array('_id' => $user->_id,
                'email' => $user->email,
                'name' => $user->name,
                'role' => 'admin'));

        $lrs->save() ? $result = true : $return = false;

        //fire a create lrs event if it worked and saced
        if ($result) {
            \Event::fire('user.create_lrs', array('user' => $user));
            \Event::fire('lrs.create', array('lrs' => $lrs));
        }


        return $result;
    }

    public function update($id, $input)
    {
        $lrs = $this->find($id);

        $validator = $lrs->validate($input);
        if ($validator->fails()) {
            return \Redirect::back()->withErrors($validator->messages());
        }

        $lrs->title = $input['title'];
        $lrs->description = $input['description'];
        $lrs->auth_service = $input['auth_service'];
        $lrs->auth_service_url = isset($input['auth_service_url']) ? $input['auth_service_url'] : '';
        $lrs->auth_cache_time = isset($input['auth_cache_time']) ? $input['auth_cache_time'] : '';
        $lrs->token = isset($input['token']) ? $input['token'] : '';
        $lrs->subdomain = isset($input['subdomain']) ? $input['subdomain'] : '';

        $lrs->save();

        return $lrs;
    }

    public function delete($id)
    {
        $lrs = $this->find($id);

        //first delete all statements
        //now delete the lrs
        return $lrs->delete();
    }

    public function removeUser($id, $user)
    {
        return $this->lrs->where('_id', $id)->pull('users', array('_id' => $user));
    }

    public function getLrsOwned($user)
    {
        return $this->lrs->where('owner._id', $user)->select('title')->get()->toArray();
    }

    public function getLrsMember($user)
    {
        return $this->lrs->where('users.user', $user)->select('title')->get()->toArray();
    }

    public function changeRole($id, $user, $role)
    {
        $lrs = $this->find($id);
        $users = $lrs->users;
        foreach ($users as &$u) {
            if ($u['_id'] == $user) {
                $u['role'] = $role;
            }
        }
        $lrs->users = $users;

        return $lrs->save();
    }

}
