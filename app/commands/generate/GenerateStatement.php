<?php

namespace app\commands\generate;

use Faker;
use User;
use Lrs;

class GenerateStatement implements GenerateInterface {

  protected $lrs;

  public function __construct() {
    $lrs = Lrs::all();
    $is_empty = $lrs->isEmpty();
    if (!$is_empty) {
      $this->lrs = $lrs->random(1);
    } else {
      $faker = Faker\Factory::create('en_GB');
      $faker->addProvider(new Faker\Provider\en_GB\Address($faker));
      $faker->addProvider(new Faker\Provider\en_GB\Internet($faker));

      $user = User::first();
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
      $this->lrs = $lrs;
    }
  }

  public function generate() {
    $faker = Faker\Factory::create('en_GB');
    $faker->addProvider(new Faker\Provider\en_GB\Address($faker));
    $faker->addProvider(new Faker\Provider\en_GB\Internet($faker));
    
    // List verb.
    $verbs = array(
      'answered', 'asked', 'attempted', 'attended', 'commented', 'completed',
      'exited', 'experienced', 'failed', 'imported', 'initialized', 'interacted',
      'launched', 'mastered', 'passed', 'preferred', 'progressed', 'registered',
      'responded', 'resumed', 'scored', 'shared', 'suspended', 'terminated', 'voided',
    );
    
    // Random verb.
    $key = array_rand($verbs);
    $verb = $verbs[$key];
    $verb = array(
      "id" => "http://adlnet.gov/expapi/verbs/{$verb}",
      "display" => array("en-US" => $verb)
    );
    
    $vs = array(
      'actor' => array(
        'objectType' => 'Agent',
        'mbox' => "mailto:{$faker->email}",
        'name' => $faker->userName
      ),
      'verb' => $verb,
      'context' => array(
        "contextActivities" => array(
          "parent" => array(
            "id" => $faker->url,
            "objectType" => "Activity"
          ),
          "grouping" => array(
            "id" => $faker->url,
            "objectType" => "Activity"
          )
        )
      ),
      "object" => array(
        "id" => $faker->url,
        "objectType" => "Activity",
        "definition" => array(
          "name" => array(
            "en-US" => "Scoring"
          ),
          "description" => array(
            "en-US" => $faker->sentence
          )
        )
      ),
      "authority" => array(
        "name" => $faker->userName,
        "mbox" => "mailto:{$faker->email}",
        "objectType" => "Agent"
      ),
    );

    // Load object of ioc container.
    $statement = \App::make('Locker\Repository\Statement\EloquentStatementRepository');

    // create statement with EloquentStatementRepository
    $statement->create(array($vs), $this->lrs);
  }

}
