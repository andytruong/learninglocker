<?php

use \app\locker\helpers\Helpers as helpers;

class AduroLrsControllerTest extends TestCase
{
	public function setUp()
    {
        parent::setUp();

        Route::enableFilters();        
    }

    public function testGetLRS()
    {   
        //test get all
        $response = $this->call("GET", '/aduro/lrs');
        $responseContent = json_decode($response->getContent());
        $this->assertTrue(property_exists($responseContent, 'lrs'));

        //test show lrs by id
        $lrs = array(
            'title' => helpers::getRandomValue(),
            'description' => 'testing description',
            'auth_service' => 3
        );
        $responseLrs = $this->call("POST", '/aduro/lrs/create', [], [], [], json_encode($lrs));
        $responseContent = json_decode($responseLrs->getContent());
        $response = $this->call("GET", '/aduro/lrs', ['lrsId' => $responseContent->new_lrs]);
        $responseContent = json_decode($response->getContent());

        $this->assertTrue(count($responseContent->lrs) == 1);

    }
    
    public function testCreateLRS()
    {
        $lrs = array(
            'title' => helpers::getRandomValue(),
            'description' => 'testing description',
            'auth_service' => 3
        );

        $response = $this->call("POST", '/aduro/lrs/create', [], [], [], json_encode($lrs));

        $responseData = $response->getData();

        $responseStatus = $response->getStatusCode();

        $checkResponse = $responseStatus == 200 && property_exists($responseData, 'success') 
            && $responseData->success === true;

        $this->assertTrue($checkResponse);
    }
    
    public function testDeleteLRS()
    {
        $lrs = array(
            'title' => helpers::getRandomValue(),
            'description' => 'testing description',
            'auth_service' => 3
        );

        $responseLrs = $this->call("POST", '/aduro/lrs/create', [], [], [], json_encode($lrs));
        $responseContent = json_decode($responseLrs->getContent());
        $deleteParam = ['lrsId' => $responseContent->new_lrs];
        $response = $this->call("POST", '/aduro/lrs/delete', [], [], [], json_encode($deleteParam));
        $responseContent = json_decode($response->getContent());

        $this->assertTrue($responseContent->success === true);
    }



}