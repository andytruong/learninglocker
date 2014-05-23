<?php

namespace app\locker\statements;

# implements xAPIValidationInterface

interface xAPIValidationInterface
{

    /**
     * Get supporting xAPI version.
     *
     * @return string
     */
    public function getSpecificationVersion();

    /**
     * Main method to run validation.
     *
     * @param  array   $statement    The statement.
     * @param  array   $authority    The authority storing statement.
     * @return array   An array containing status, errors (if any) and the statement
     */
    public function validate($statement = array(), $authority = array());

}
