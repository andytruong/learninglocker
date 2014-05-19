<?php

use \app\locker\helpers\Helpers as helpers;

class AduroLrsControllerTest extends TestCase
{
	public function setUp()
    {
        parent::setUp();

        Route::enableFilters();        
    }

    public function testCreateLRS()
    {
        $lrs = array(
            'title' => helpers::getRandomValue(),
            'description' => helpers::getRandomValue(),
            'auth_service' => 3
        );

        $response = $this->call("POST", '/lrs-create', [], [], [], json_encode($lrs));

        $responseData = $response->getData();

        $responseStatus = $response->getStatusCode();

        $checkResponse = $responseStatus == 200 && property_exists($responseData, 'success') 
            && $responseData->success === true;

        $this->assertTrue($checkResponse);
    }
}