<?php

namespace app\commands\generate;

use Faker;
use Lrs;
use User;

class GenerateLrs implements GenerateInterface {

  public function generate() {
    $user = User::first();

    $faker = Faker\Factory::create('en_GB');
    $faker->addProvider(new Faker\Provider\en_GB\Address($faker));
    $faker->addProvider(new Faker\Provider\en_GB\Internet($faker));
    $faker->addProvider(new Faker\Provider\Lorem($faker));

    $lrs = new Lrs;
    $lrs->title = $faker->sentence;
    $lrs->description = $faker->sentence;
    $lrs->api = array('basic_key' => \app\locker\helpers\Helpers::getRandomValue(),
      'basic_secret' => \app\locker\helpers\Helpers::getRandomValue());
    $lrs->owner = array('_id' => $user->_id);
    $lrs->users = array(array('_id' => $user->_id,
        'email' => $user->email,
        'name' => $user->name,
        'role' => 'admin'));

    $lrs->save();
  }

}
