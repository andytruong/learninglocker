<?php

namespace app\commands\generate;

use Illuminate\Console\Command;
use Faker;
use User;

class GenerateUser implements GenerateInterface {

  public function generate() {
    $faker = Faker\Factory::create('en_GB');
    $faker->addProvider(new Faker\Provider\en_GB\Address($faker));
    $faker->addProvider(new Faker\Provider\en_GB\Internet($faker));
    User::create([
      'name' => $faker->userName,
      'email' => $faker->email,
      'password' => \Hash::make($faker->userName),
      'role' => 'observer',
      'verified' => 'yes'
    ]);
  }

}
