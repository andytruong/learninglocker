<?php

require_once __DIR__ . '/BaseStatementValidationTest.php';

class StatementValidationVerbTest extends BaseStatementValidationTest
{

    /**
     * @group andy
     */
    public function testVerb()
    {
        $results = $this->exec($this->getFixturePath() . '/Invalid/Verb/missing-display.json');
        $this->assertEquals('failed', $results['status']);
        $this->assertEquals(
            '`display` is a required key and is not present in verb', trim($results['errors'][0])
        );
    }

}
