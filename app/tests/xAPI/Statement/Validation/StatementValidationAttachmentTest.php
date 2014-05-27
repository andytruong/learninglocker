<?php

require_once __DIR__ . '/BaseStatementValidationTest.php';

class StatementValidationAttachmentTest extends BaseStatementValidationTest
{

    public function testContentType()
    {
        $results = $this->exec($this->getFixturePath() . '/Invalid/Attachment/content-type-is-not-string.json');
        $this->assertEquals('failed', $results['status']);
        $this->assertEquals(
            '`contentType` is not a valid Internet Media Type in attachment', trim($results['errors'][0])
        );
    }

    public function testLength()
    {
        $results = $this->exec($this->getFixturePath() . '/Invalid/Attachment/length-is-not-integer.json');
        $this->assertEquals('failed', $results['status']);
        $this->assertEquals(
            '`length` is not a valid number in attachment', trim($results['errors'][0])
        );
    }

    public function testSha2()
    {
        $results = $this->exec($this->getFixturePath() . '/Invalid/Attachment/missing-sha2.json');
        $this->assertEquals('failed', $results['status']);
        $this->assertEquals(
            '`sha2` is a required key and is not present in attachment', trim($results['errors'][0])
        );
    }

    /**
     * @group andy
     */
    public function testUsageType()
    {
        $results = $this->exec($this->getFixturePath() . '/Invalid/Attachment/missing-usage-type.json');
        $this->assertEquals('failed', $results['status']);
        $this->assertEquals(
            '`usageType` is a required key and is not present in attachment', trim($results['errors'][0])
        );
    }

}
