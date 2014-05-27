<?php

require_once __DIR__ . '/BaseStatementValidationTest.php';

class StatementValidationAttachmentTest extends BaseStatementValidationTest
{

    public function testX()
    {
        $results = $this->exec($this->getFixturePath() . '/Invalid/Attachment/content-type-is-not-string.json');
        $this->assertEquals('failed', $results['status']);
        $this->assertEquals(
            '`contentType` is not a valid Internet Media Type in attachment',
            trim($results['errors'][0])
        );
    }

}
