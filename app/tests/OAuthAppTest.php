<?php

class OAuthAppTest extends TestCase
{
  public function testOAuthApp()
  {
    $OAuthApp = new OAuthApp;

    // Test name, website, callback, rules require
    $values = [
      'name' => '',
      'description' => \app\locker\helpers\Helpers::getRandomValue(),
      'website' => '',
      'callback' => '',
      'rules' => '',
      'client_id' => \app\locker\helpers\Helpers::getRandomValue(),
      'secret' => \app\locker\helpers\Helpers::getRandomValue(),
      'organisation' => [
        'name' => \app\locker\helpers\Helpers::getRandomValue(),
        'website' => \app\locker\helpers\Helpers::getRandomValue(),
      ],
    ];

    $validator = $OAuthApp->validate($values);
    $this->assertTrue($validator->fails());
    $this->assertFalse($validator->passes());

    $values['name'] = \app\locker\helpers\Helpers::getRandomValue();
    $values['website'] = \app\locker\helpers\Helpers::getRandomValue();
    $values['rules'] = '1';
    $values['callback'] = \app\locker\helpers\Helpers::getRandomValue();
    $validator = $OAuthApp->validate($values);
    $this->assertTrue($validator->passes());

    // Add new OAuthApp
    $OAuthApp->name = $values['name'];
    $OAuthApp->description = $values['description'];
    $OAuthApp->website = $values['website'];
    $OAuthApp->callbackurl = $values['callback'];
    $OAuthApp->rules = $values['rules'];
    $OAuthApp->client_id = $values['client_id'];
    $OAuthApp->secret = $values['secret'];
    $OAuthApp->organisation = $values['organisation'];
    $result = $OAuthApp->save();
    $this->assertTrue($result);

    // Load OAuthApp from database
    $OAuthApp_id = $OAuthApp->_id;
    $db_OAuthApp = OAuthApp::find($OAuthApp_id);
    $this->assertEquals($db_OAuthApp->_id, $OAuthApp->_id);

    // Edit OAuthApp
    $name = \app\locker\helpers\Helpers::getRandomValue();
    $db_OAuthApp->name = $name;
    $db_OAuthApp->save();
    $this->assertEquals($db_OAuthApp->name, $name);

    // Delete OAuthApp
    $db_OAuthApp = OAuthApp::find($OAuthApp->_id);
    $db_OAuthApp->delete();
    $this->assertEquals(OAuthApp::find($OAuthApp_id), NULL, 'delete OAuthApp');
  }
}
