<?php

class StatementPutTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        Route::enableFilters();

        // Authentication as super user.
        $user = User::firstOrCreate(['email' => 'quan@ll.com']);
        Auth::login($user);
    }

    private function _makeRequest($param, $auth)
    {
        return $this->call('PUT', '/data/xAPI/statements', ['statementId' => $param['id']], [], ['PHP_AUTH_USER' => $auth['user'],
                'PHP_AUTH_PW' => $auth['pass'],
                'HTTP_X-Experience-API-Version' => '1.0.1'
                ], json_encode($param)
        );
    }

    /**
     * Create statements for lrs
     *
     * @return void
     */
    public function testPutBehavior()
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

        //case: conflict-matches
        $response = $this->_makeRequest($param, $auth);
        $responseData = $response->getData();
        $responseStatus = $response->getStatusCode();
        $checkResponse = $responseStatus == 204 && empty($responseData);

        $this->assertTrue($checkResponse);

        // case: conflict nomatch
        $param['result'] = array();
        $response = $this->_makeRequest($param, $auth);
        $responseData = $response->getData();
        $responseStatus = $response->getStatusCode();
        $checkResponse = $responseStatus == 409 && property_exists($responseData, 'success') && !$responseData->success;

        $this->assertTrue($checkResponse);
    }

    /**
     * Create statements for lrs with Auth Service
     *
     * @return void
     */
    public function testPutAuthService()
    {
        $this->lrsAuthMethod = 3;
        $this->auth_service_url = 'http://auth.aduro.go1.com.vn/client';
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

        $response = $this->_makeRequest($param, $auth);
        $responseData = $response->getData();
        $responseStatus = $response->getStatusCode();

        $checkResponse = $responseStatus == 204 && empty($responseData);

        $this->assertTrue($checkResponse);
    }

    public function tearDown()
    {
        parent::tearDown();

        /**
         *   Need LRS table is empty because waiting the getLrsBySubdomain()
         */
        $this->lrs->delete();
    }

}
