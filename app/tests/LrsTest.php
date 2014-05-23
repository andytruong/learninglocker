<?php

class LrsTest extends TestCase
{

    /**
     * Test LRS
     */
    public function testLRS()
    {
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
}
