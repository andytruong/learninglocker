<?php namespace Locker\Repository\Site;

use Site;

class EloquentSiteRepository implements SiteRepository
{
  public function all()
  {
    return Site::get()->first();
  }

  public function find($id)
  {
    return Site::find($id);
  }

  public function validate($data)
  {
    $site = new Site;

    return $site->validate( $data );
  }

  public function create($data)
  {
    $site            = new Site;
    $site->name        = $data['name'];
    $site->description = $data['description'];
    $site->email       = $data['email'];
    $site->create_lrs  = array('super');
    $site->registration = $data['registration'];
    $site->restrict    = $data['restrict']; //restrict registration to a specific email domain
    $site->domain      = $data['domain'];
    $site->super       = array( array('user' => \Auth::user()->_id ) );
    $site->footer_sitename = 'Aduro LRS';
    $site->footer_url = 'http://adurolms.com';
    $site->save();

    return $site;

  }

  public function update($id, $data)
  {
    $site = $this->find( $id );
    $site->name        = $data['name'];
    $site->description = $data['description'];
    $site->email       = $data['email'];
    $site->create_lrs  = $data['create_lrs'];
    $site->registration = $data['registration'];
    $site->domain      = $data['domain']; //restrict registration to a specific email domain

    $site->auth_token = $data['auth_token'];
    $site->auth_service_url = $data['auth_service_url'];
    $site->auth_cache_time = $data['auth_cache_time'];
    
    $site->footer_sitename = $data['footer_sitename'];
    $site->footer_url = $data['footer_url'];

    return $site->save();

  }

  public function delete($id)
  {
    $site = $this->find($id);

    return $site->delete();
  }

  public function verifyUser($user_id)
  {
    //check user exists
    $user = \User::find( $user_id );
    if ($user) {
      if ($user->verified == 'yes') {
        $user->verified = 'no';
      } else {
        $user->verified = 'yes';
      }
      $user->save();
    }

    return $user->verified;

  }

}
