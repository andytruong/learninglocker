<?php

use \app\locker\helpers\Helpers as helpers;

class StatementGetTest extends TestCase
{
	public function setUp()
    {
        parent::setUp();

        Route::enableFilters();        
    }

    public function testCreateLRS()
    {
    	$user = User::firstOrCreate(['email' => 'quan@ll.com']);
        //need a fix user in system
        $user = array(
            'email' =>  'quan@ll.com',
            'pass'  => '1' 
        );
        $lrs = array(
            'title' => helpers::getRandomValue(),
            'description' => helpers::getRandomValue(),
            'auth_service' => 3
        );

        $response = $this->call("POST", '/lrs-create', 
        	[], 
        	[], 
        	['PHP_AUTH_USER' => $user['email'],
                'PHP_AUTH_PW' => $user['pass']
            ],
            json_encode($lrs)
        );

        $responseData = $response->getData();

        $responseStatus = $response->getStatusCode();

        $checkResponse = $responseStatus == 200 && property_exists($responseData, 'success') 
            && $responseData->success === true;

        $this->assertTrue($checkResponse);
    }
}