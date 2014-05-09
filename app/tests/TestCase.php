<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase {

  /**
   * Creates the application.
   *
   * @return \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  public function createApplication() {
    $unitTesting = true;

    $testEnvironment = 'testing';

    return require __DIR__ . '/../../bootstrap/start.php';
  }

  /**
   * Create dummy LRS
   * @return \Lrs
   */
  protected function createLRS() {
    $lrs = new Lrs;
    $lrs->title = \app\locker\helpers\Helpers::getRandomValue();
    $lrs->description = \app\locker\helpers\Helpers::getRandomValue();
    $lrs->api = array('basic_key' => \app\locker\helpers\Helpers::getRandomValue(),
      'basic_secret' => \app\locker\helpers\Helpers::getRandomValue()
    );

    $lrs->owner = array('_id' => Auth::user()->_id);
    $lrs->users = array(
      array('_id' => Auth::user()->_id,
        'email' => Auth::user()->email,
        'name' => Auth::user()->name,
        'role' => 'admin'
      )
    );

    $lrs->save();
    $this->lrs = $lrs;
    return $lrs;
  }

  /**
   * Return default statement data.
   */
  protected function defaultStatment() {
    return array(
      'actor' => array(
        'objectType' => 'Agent',
        'mbox' => 'mailto:duy.nguyen@go1.com.au',
        'name' => 'duynguyen'
      ),
      'verb' => array(
        "id" => "http://adlnet.gov/expapi/verbs/experienced",
        "display" => array("und" => "experienced")
      ),
      'context' => array(
        "contextActivities" => array(
          "parent" => array(
            "id" => "http://tincanapi.com/GolfExample_TCAPI",
            "objectType" => "Activity"
          ),
          "grouping" => array(
            "id" => "http://tincanapi.com/GolfExample_TCAPI",
            "objectType" => "Activity"
          )
        )
      ),
      "object" => array(
        "id" => "http://tincanapi.com/GolfExample_TCAPI/Playing/Scoring.html",
        "objectType" => "Activity",
        "definition" => array(
          "name" => array(
            "en-US" => "Scoring"
          ),
          "description" => array(
            "en-US" => "An overview of how to score a round of golf."
          )
        )
      ),
      "authority" => array(
        "name" => "",
        "mbox" => "mailto:quan@ll.com",
        "objectType" => "Agent"
      ),
    );
  }

  /**
   * Create dummy statement with lrs
   * @param type $lrs
   * @return type
   */
  protected function createStatement($vs, $lrs) {
    // Load object of ioc container.
    $statement = App::make('Locker\Repository\Statement\EloquentStatementRepository');

    // create statement with EloquentStatementRepository
    $result = $statement->create(array($vs), $lrs);
    return $result;
  }

}
