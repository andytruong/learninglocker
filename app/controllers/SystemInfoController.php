<?php

class SystemInfoController extends BaseController
{

    public function terms()
    {
        return View::make('partials.pages.terms');
    }

    public function tools()
    {
        return View::make('partials.pages.tools', array('tools' => true));
    }

    public function help()
    {
        return View::make('partials.pages.help', array('help' => true));
    }

    public function about()
    {
        return View::make('partials.pages.about');
    }

}
