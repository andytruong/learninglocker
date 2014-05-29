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

    private function _makeRequest($auth, $version)
    {
        return $this->call("GET", '/data/xAPI/statements', [], [], [
                'PHP_AUTH_USER' => $auth['user'],
                'PHP_AUTH_PW' => $auth['pass'],
                'HTTP_X-Experience-API-Version' => $version
            ]
        );
    }

    /**
     * Create statements for lrs
     *
     * @param string $version Make sure LRS response to all valid version.
     * @return void
     * @dataProvider dataGetAuthService
     */
    public function testGetAuthService($version)
    {
        $lrs = $this->createLRS();
        $this->lrsAuthMethod = 3;

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
        $response = $this->_makeRequest($auth, $version);
        $this->assertEquals($response->getStatusCode(), 200);

        $lrs->delete();
    }

    public function dataGetAuthService() {
        $data = array();

        foreach (range(0, 20) as $i) {
            if (array_rand([true, false])) {
                $data[][] = "1.0.{$i}";
            }
        }

        return $data;
    }
}
