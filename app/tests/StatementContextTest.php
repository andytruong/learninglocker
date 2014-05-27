<?php

/**
 * 
 */
class StatementContextTest extends TestCase {

    public function setUp() {
        parent::setUp();
        // Authentication as super user.
        $user = User::firstOrCreate(array('email' => $this->dummyEmail()));
        Auth::login($user);
        $this->lrs = $this->createLRS();
        $this->statement = App::make('Locker\Repository\Statement\EloquentStatementRepository');
    }

    /**
     * The LRS MUST return every value in the contextActivities Object as an array, 
     * even if it arrived as a single Activity object
     */
    public function testContext1() {
    }

    /**
     * The LRS MUST return single Activity Objects as an array of length one containing the same Activity.
     */
    public function testContext2() {
        $stmt = $this->defaultStatment();
        $parent = new stdClass();
        $parent->id = 'http://tincanapi.com/GolfExample_TCAPI';
        $parent->objectType = 'Activity';
        $contextActivities = array(
            "parent" => $parent,
        );
        $stmt['context']['contextActivities'] = $contextActivities;
        $return = $this->createStatement($stmt, $this->lrs);
        $id = $return['ids'][0];
        $stmt = $this->statement->find($id);
        // The parent must be array.
        $this->assertTrue(is_array($stmt['statement']['context']['contextActivities']['parent']));
    }

}
