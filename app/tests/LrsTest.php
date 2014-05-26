<?php

class LrsTest extends TestCase {

    public function setUp()
    {
        parent::setUp();

        Route::enableFilters();

        // Authentication as super user.
        $user = User::firstOrCreate(['email' => 'quan@ll.com']);
        Auth::login($user);
    }
    
    /**
     * Test LRS
     */
    public function testLRS() {
        $lrs = new Lrs;

        // Test title required.
        $values = array(
            'title' => '',
            'description' => \app\locker\helpers\Helpers::getRandomValue(),
            'api' => array('basic_key' => \app\locker\helpers\Helpers::getRandomValue(),
                'basic_secret' => \app\locker\helpers\Helpers::getRandomValue()),
            'auth_service' => \Lrs::INTERNAL_LRS
        );
        $validator = $lrs->validate($values);
        $this->assertTrue($validator->fails());
        $this->assertFalse($validator->passes());

        $values['title'] = \app\locker\helpers\Helpers::getRandomValue();
        $validator = $lrs->validate($values);
        $this->assertTrue($validator->passes());

        // Validate auth_service
        $values['auth_service'] = \Lrs::ADURO_AUTH_SERVICE;
        $validator = $lrs->validate($values);
        $this->assertTrue($validator->fails());

        // Fails if auth_service_url is empty
        $values['token'] = \app\locker\helpers\Helpers::getRandomValue();
        $validator = $lrs->validate($values);
        $this->assertTrue($validator->fails());

        $values['auth_service_url'] = 'http://' . \app\locker\helpers\Helpers::getRandomValue() . '.adurolms.com';
        $validator = $lrs->validate($values);
        $this->assertTrue($validator->passes());

        // Add new lrs
        $lrs->title = $values['title'];
        $lrs->description = $values['description'];
        $lrs->api = $values['api'];
        $result = $lrs->save();
        $this->assertTrue($result);

        // Load lrs from db
        $lrs_id = $lrs->_id;
        $db_lrs = Lrs::find($lrs_id);
        $this->assertEquals($db_lrs->_id, $lrs->_id);

        // Edit lrs
        $title = \app\locker\helpers\Helpers::getRandomValue();
        $db_lrs->title = $title;
        $db_lrs->save();
        $this->assertEquals($db_lrs->title, $title);

        // Delete lrs
        $db_lrs->delete();
        $this->assertEquals(Lrs::find($lrs_id), NULL, 'delete lrs');
    }

    public function testInternalAuthentication() {
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

        \Config::set('testing.auth_type', -1);
        // Make sure response data for the get request
        $response = $this->_makeRequest($auth);
        $this->assertEquals($response->getStatusCode(), 200);
        
        $auth_type = \Config::get('testing.auth_type');
        $this->assertEquals($auth_type, \Lrs::INTERNAL_LRS);
        
    }
    
    public function testAuthenticationService() {
        $this->lrsAuthMethod = \Lrs::ADURO_AUTH_SERVICE;
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

        \Config::set('testing.auth_type', -1);
        // Make sure response data for the get request
        $response = $this->_makeRequest($auth, ['auth_type' => 'central']);
        $this->assertEquals($response->getStatusCode(), 200);
        
        $auth_type = \Config::get('testing.auth_type');
        $this->assertEquals($auth_type, \Lrs::ADURO_AUTH_SERVICE);
        
    }
    
    private function _makeRequest($auth, $param = [])
    {
        return $this->call("GET", '/data/xAPI/statements', $param, [], ['PHP_AUTH_USER' => $auth['user'],
                'PHP_AUTH_PW' => $auth['pass'],
                'HTTP_X-Experience-API-Version' => '1.0.1'
                ]
        );
    }

}
