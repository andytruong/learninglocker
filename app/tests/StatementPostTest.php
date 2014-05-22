<?php

class StatementPostTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        Route::enableFilters();

        // Authentication as super user.
        $user = User::firstOrCreate(['email' => 'quan@ll.com']);
        Auth::login($user);
    }

    private function _makeRequest($param, $method, $auth)
    {
        return $this->call($method, '/data/xAPI/statements',
            [],
            [],
            ['PHP_AUTH_USER' => $auth['user'],
                'PHP_AUTH_PW' => $auth['pass'],
                'HTTP_X-Experience-API-Version' => '1.0.1'
            ],
            !empty($param) ? json_encode($param) : []
        );
    }

    /**
     * make a post request to LRS
     *
     * @return void
     */
    public function testPostBehavior()
    {
        $this->lrsAuthMethod = Lrs::INTERNAL_LRS;
        $this->createLRS();

        $vs = $this->defaultStatment();
        $result = $this->createStatement($vs, $this->lrs);

        $statement = App::make('Locker\Repository\Statement\EloquentStatementRepository');
        $createdStatement = $statement->find($result['ids'][0]);

        $param = array(
            'actor' => $createdStatement->statement['actor'],
            'verb' => $createdStatement->statement['verb'],
            'context' => $createdStatement->statement['context'],
            'object' => $createdStatement->statement['object'],
            'id' => $createdStatement->statement['id'],
            'timestamp' => $createdStatement->statement['timestamp'],
        );

        $auth = array(
            'user' => $this->lrs->api['basic_key'],
            'pass' => $this->lrs->api['basic_secret'],
        );

        // case: conflict-matches
        $response = $this->_makeRequest($param, "POST", $auth);
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getData());

        // case: conflict nomatch
        $param['result'] = array();
        $response = $this->_makeRequest($param, "POST", $auth);
        $responseData = $response->getData();
        $this->assertEquals(409, $response->getStatusCode());
        $this->assertTrue(property_exists($responseData, 'success') && !$responseData->success);

        // Make sure response data for the get request
        $responseGet = $this->_makeRequest(array(), "GET", $auth);
        $this->assertEquals($responseGet->getStatusCode(), 200);

        // Make sure response data for the get request
        unset($param['result']);
        $responsePost = $this->_makeRequest($param, "POST", $auth);
        $this->assertEquals($responsePost->getStatusCode(), 204);
    }

    /**
     * make a post request to lrs with Auth Service
     *
     * @return void
     */
    public function testPostAuthService()
    {
        $this->lrsAuthMethod = 3;
        $this->createLRS();

        $vs = $this->defaultStatment();
        $result = $this->createStatement($vs, $this->lrs);

        $statement = App::make('Locker\Repository\Statement\EloquentStatementRepository');
        $createdStatement = $statement->find($result['ids'][0]);

        $param = [
            'actor' => $createdStatement->statement['actor'],
            'verb' => $createdStatement->statement['verb'],
            'context' => $createdStatement->statement['context'],
            'object' => $createdStatement->statement['object'],
            'id' => $createdStatement->statement['id'],
            'timestamp' => $createdStatement->statement['timestamp'],
        ];

        //create client for Auth Service
        $authBody = [
             'api_key' => $this->lrs->api['basic_key'],
             'api_secret' => $this->lrs->api['basic_secret'],
         ];

        $authClient = $this->createClientAuth($authBody);

        $auth = array(
            'user' => $authClient['name'],
            'pass' => $authClient['api_secret'],
        );

        $response = $this->_makeRequest($param, "POST", $auth);
        $responseData = $response->getData();
        $responseStatus = $response->getStatusCode();
        $checkResponse = $responseStatus == 204 && empty($responseData);

        $this->assertTrue($checkResponse);
    }

}
