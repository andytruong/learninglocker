<?php

use \app\locker\helpers\Helpers as helpers;

class AduroLrsControllerTest extends TestCase
{

    // public $authPassword;

    public function setUp()
    {
        parent::setUp();
        Route::enableFilters();

        $this->timestamp = time();
        $this->authUser = 'kien';
        $api_key = 'abcded';
        $api_secret = '-----BEGIN PUBLIC KEY-----MFwwDQYJKoZIhvcNAQEBBQADSwAwSAJBANH9tnNhhmbwLRcaV1rJLvcix/Ol7mreCtmleIFzCFDx2ni9Sub7o58K7h4AHoKoBUv0JdQBPTGnjqT/Nhy6QqkCAwEAAQ==-----END PUBLIC KEY-----';

        $this->authPassword = base64_encode(hash_hmac('sha256', "{$api_key}{$this->timestamp}", $api_secret));

        $site = \Site::first();
        $site->auth_token = 'aduro';
        $site->auth_service_url = 'http://auth.services.adurolms.com';
        $site->use_auth = 0;
        $site->save();
    }

    public function testGetLRS()
    {
        // test get all
        $response = $this->call("GET", '/resource/lrs', ['timestamp' => $this->timestamp], [], ['PHP_AUTH_USER' => $this->authUser,
            'PHP_AUTH_PW' => $this->authPassword
            ]
        );
        $responseContent = json_decode($response->getContent());
        $this->assertTrue(property_exists($responseContent, "lrs"));

        // test show lrs by id
        $lrs = array(
            'title' => helpers::getRandomValue(),
            'description' => 'testing description',
            'auth_service' => 3
        );
        $responseLrs = $this->call("POST", '/resource/lrs', ['timestamp' => $this->timestamp], [], ['PHP_AUTH_USER' => $this->authUser,
            'PHP_AUTH_PW' => $this->authPassword
            ], json_encode($lrs)
        );
        $responseContent = json_decode($responseLrs->getContent());
        $response = $this->call("GET", "/resource/lrs/$responseContent->new_lrs", ['timestamp' => $this->timestamp], [], ['PHP_AUTH_USER' => $this->authUser,
            'PHP_AUTH_PW' => $this->authPassword
            ]
        );
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

        $response = $this->call("POST", '/resource/lrs', ['timestamp' => $this->timestamp], [], ['PHP_AUTH_USER' => $this->authUser,
            'PHP_AUTH_PW' => $this->authPassword
            ], json_encode($lrs)
        );

        $responseData = $response->getData();

        $responseStatus = $response->getStatusCode();

        $checkResponse = $responseStatus == 200 && property_exists($responseData, 'success') && $responseData->success === true;

        $this->assertTrue($checkResponse);
    }

    public function testUpdateLRS()
    {
        $lrs = array(
            'title' => helpers::getRandomValue(),
            'description' => 'testing description',
            'auth_service' => 3
        );
        $responseLrs = $this->call("POST", '/resource/lrs', ['timestamp' => $this->timestamp], [], ['PHP_AUTH_USER' => $this->authUser,
            'PHP_AUTH_PW' => $this->authPassword
            ], json_encode($lrs)
        );
        $responseContent = json_decode($responseLrs->getContent());
        $updateParam = [
            'title' => 'update' . helpers::getRandomValue(),
            'description' => 'testing description',
            'auth_service' => 3
        ];
        $response = $this->call("PUT", "/resource/lrs/$responseContent->new_lrs", ['timestamp' => $this->timestamp], [], ['PHP_AUTH_USER' => $this->authUser,
            'PHP_AUTH_PW' => $this->authPassword
            ], json_encode($updateParam)
        );

        $responseContent = json_decode($response->getContent());

        $this->assertTrue($responseContent->success === true);
    }

    public function testDeleteLRS()
    {
        $lrs = array(
            'title' => helpers::getRandomValue(),
            'description' => 'testing description',
            'auth_service' => 3
        );

        $responseLrs = $this->call("POST", '/resource/lrs', ['timestamp' => $this->timestamp], [], ['PHP_AUTH_USER' => $this->authUser,
            'PHP_AUTH_PW' => $this->authPassword
            ], json_encode($lrs)
        );
        $responseContent = json_decode($responseLrs->getContent());
        $response = $this->call('DELETE', "/resource/lrs/$responseContent->new_lrs", ['timestamp' => $this->timestamp], [], ['PHP_AUTH_USER' => $this->authUser,
            'PHP_AUTH_PW' => $this->authPassword]
        );
        $responseContent = json_decode($response->getContent());

        $this->assertTrue($responseContent->success === true);
    }

}
