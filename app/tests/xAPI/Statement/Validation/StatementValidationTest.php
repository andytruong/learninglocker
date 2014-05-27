<?php

use app\locker\statements\xAPIValidation as StatementValidationManager;

class StatementValidationTest extends PHPUnit_Framework_TestCase
{
    protected function getFixturePath() {
        return __DIR__ . '/../../../Fixtures/Statements';
    }

    protected function exec($path) {
        $json = file_get_contents($path);
        $statement = json_decode($json, true);
        $manager = new StatementValidationManager();
        return $manager->validate($statement);
    }

    /**
     * @dataProvider dataProviderSimple
     */
    public function testSimple($path) {
        $results = $this->exec($path);
        $this->assertEquals('passed', $results['status']);
        $this->assertEmpty($results['errors']);
    }

    public function dataProviderSimple() {
        $data = array();
        
        foreach (glob($this->getFixturePath() . "/Valid/*.json") as $file) {
            $data[][] = $file;
        }

        return $data;
    }
}
