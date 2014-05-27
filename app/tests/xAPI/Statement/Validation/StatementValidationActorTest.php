<?php

require_once __DIR__ . '/BaseStatementValidationTest.php';

class StatementValidationActorTest extends BaseStatementValidationTest
{

    public function testMissingActor()
    {
        $results = $this->exec($this->getFixturePath() . '/Invalid/Actor/missing-actor.json');
        $this->assertEquals('failed', $results['status']);
        $this->assertEquals(
            '`actor` is a required key and is not present in core statement',
            trim($results['errors'][0])
        );
    }

    public function testGroupMissingMember() {
        $results = $this->exec($this->getFixturePath() . '/Invalid/Actor/Group/missing-member.json');
        $this->assertEquals('failed', $results['status']);
        $this->assertNotEmpty($results['errors'][0]);
    }

}
