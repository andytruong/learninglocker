<?php

/**
 * Ref http://zackpierce.github.io/xAPI-Validator-JS/
 */

use app\locker\statements\xAPIValidation as StatementValidationManager;

abstract class BaseStatementValidationTest extends PHPUnit_Framework_TestCase
{

    protected function getFixturePath()
    {
        return __DIR__ . '/../../../Fixtures/Statements';
    }

    protected function exec($path)
    {
        $json = file_get_contents($path);
        $statement = json_decode($json, true);
        $manager = new StatementValidationManager();
        return $manager->validate($statement);
    }

}
