<?php

namespace app\locker\helpers;

class Lrs
{

    /**
     * @param $role  Can the current user create LRS based on their role?
     *
     * @return boolean
     * */
    public static function lrsCanCreate()
    {
        $site = \Site::first();

        if (in_array(\Auth::user()->role, $site->create_lrs)) {
            return true;
        }

        return false;
    }

    /**
     * @param $lrs  Can the current user access based on passed role requirement
     *
     * @return boolean
     * */
    public static function lrsAdmin($lrs)
    {
        $user = \Auth::user();

        //get all users with access to the lrs
        foreach ($lrs->users as $u) {
            $get_users[] = $u['_id'];
        }

        //check current user is in the list of allowed users and is an admin
        if (!in_array($user->_id, $get_users) && $user->role == 'admin') {
            return true;
        }

        return false;
    }

    /**
     * @param $lrs  Can the current user edit lrs
     *
     * @return boolean
     * */
    public static function lrsEdit($lrs)
    {
        $user = \Auth::user();

        //get all users with admin access to the lrs
        foreach ($lrs->users as $u) {
            if ($u['role'] == 'admin') {
                $get_users[] = $u['_id'];
            }
        }

        //check current user is in the list of allowed users and is an admin
        if (in_array($user->_id, $get_users) || $user->role == 'super') {
            return true;
        }

        return false;
    }

    /**
     * Is user the owner of LRS (or site super admin)
     *
     * @return boolean
     *
     * */
    public static function lrsOwner($lrs_id)
    {
        $lrs = \Lrs::find($lrs_id);
        if ($lrs->owner['_id'] == \Auth::user()->_id || \Auth::user()->role == 'super') {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Is a user, a member of an LRS?
     *
     * @param $string $lrs
     * @param $string $user
     *
     * @return boolean
     *
     * */
    public static function isMember($lrs, $user)
    {
        $isMember = \Lrs::where('users._id', $user)->where('_id', $lrs)->first();
        if ($isMember) {
            return true;
        }

        return false;
    }

    /**
     * Get LRS by subdomain.
     * */
    public static function getLrsBySubdomain()
    {
        $subs = explode(".", $_SERVER['SERVER_NAME']);
        $subdomain = reset($subs);

        $lrs = \Lrs::where('subdomain', '=', $subdomain)->get()->first();
        if (is_null($lrs)) {
            $lrs = \Lrs::where('title', '=', $subdomain)->get()->first();
        }

        if (is_null($lrs)) {
            throw new \Exception('Cant load lrs from domain', '404');
        }
        return $lrs;
    }

}
