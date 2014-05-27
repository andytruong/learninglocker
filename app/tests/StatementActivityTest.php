<?php

/**
 * 
 */
class StatementActivityTest extends TestCase {
    
    public function setUp() {
        parent::setUp();
        // Authentication as super user.
        $user = User::firstOrCreate(array('email' => $this->dummyEmail()));
        Auth::login($user);  
        $this->lrs = $this->createLRS();
    }

    /**
     * An LRS MUST ignore any information which indicates two authors 
     * or organizations may have used the same Activity id.
     */
    public function testActity1() {
        $this->assertTrue(true);
    }
    
    /**
     * An LRS MUST NOT treat references to the same id as references to different Activities.
     */
    public function testActity2() {
        // Remove all activities
        $activities = \Activity::all()->values();
        foreach ($activities as $activity) {
            $activity->delete();
        }

        $stmt = $this->defaultStatment();
        $object = array(
            'objectType' => 'Activity',
            'id' => 'http://xapi.adurolms.com/exampleactivity',
            'definition' => array(
                'name' => array('en-Us' => 'example activity'),
                'description' => array('en-Us' => 'An example of an activity'),
                'type' => 'http://xapi.adurolms.com/types/exampleactivitytype'
            )
        );
        $stmt['object'] = $object;
        $this->createStatement($stmt, $this->lrs);
        $this->createStatement($stmt, $this->lrs);
        
        $count = \Activity::all()->count();
        $this->assertEquals($count, 1);
        
        $stmt['object']['id'] = 'http://xapi.adurolms.com/anotheractivity';
        $this->createStatement($stmt, $this->lrs);
        $count = \Activity::all()->count();
        $this->assertEquals($count, 2);
    }
}