<?php

class StatementGetTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        Route::enableFilters();

        // Authentication as super user.
        $user = User::firstOrCreate(['email' => 'quan@ll.com']);
        Auth::login($user);
    }

    private function _makeRequest($auth)
    {
        return $this->call("GET", '/data/xAPI/statements', [], [], ['PHP_AUTH_USER' => $auth['user'],
                'PHP_AUTH_PW' => $auth['pass'],
                'HTTP_X-Experience-API-Version' => '1.0.1'
                ]
        );
    }

    /**
     * Create statements for lrs
     *
     * @return void
     */
    public function testGetAuthService()
    {
        $this->lrsAuthMethod = 3;
        $this->createLRS();

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

        // Make sure response data for the get request
        $response = $this->_makeRequest($auth);
        $this->assertEquals($response->getStatusCode(), 200);
    }

}
