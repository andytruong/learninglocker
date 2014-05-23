<?php

class SiteFrontController extends BaseController
{

    public function front()
    {
        if (Auth::check()) {
            return $this->frontSuper();
        }

        return $this->frontNormal();
    }

    protected function frontSuper()
    {
        // if super admin, show site dashboard, otherwise show list of LRSs can access
        if (Auth::user()->role === 'super') {
            return View::make('partials.site.dashboard', array(
                    'site' => \Site::first(),
                    'list' => Lrs::all(),
                    'dash_nav' => true
            ));
        }

        $lrs = Lrs::where('users._id', \Auth::user()->_id)->get();
        return View::make('partials.lrs.list', array('lrs' => $lrs, 'list' => $lrs, 'site' => $site));
    }

    protected function frontNormal()
    {
        if ($site = \Site::first()) {
            return View::make('system.forms.login', array('site' => $site));
        }

        return View::make('system.forms.register');
    }

}
