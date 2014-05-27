<?php

require_once __DIR__ . '/BaseStatementValidationTest.php';

class StatementValidationTest extends BaseStatementValidationTest
{
    /**
     * @dataProvider dataProviderSimple
     */
    public function testSimple($path) {
        $results = $this->exec($path);
        $this->assertEquals('passed', $results['status']);
        $this->assertEmpty($results['errors']);
    }

    public function dataProviderSimple() {
        $data = [];

        foreach (['', 'Object', 'Verb/Display'] as $k) {
            foreach (glob($this->getFixturePath() . "/Valid/{$k}/*.json") as $file) {
                $data[][] = $file;
            }
        }

        return $data;
    }
}
