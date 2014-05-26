<?php

/*
  |-----------------------------------------------------------------------------------
  |
  | Validate TinCan (xAPI) statements. You can read more about the
  | standard here http://www.adlnet.gov/wp-content/uploads/2013/05/20130521_xAPI_v1.0.0-FINAL-correx.pdf
  |
  | This class covers version 1.0.0 and was built as part of the HT2 Learning Locker project.
  | http://learninglocker.net
  |
  | @author Dave Tosh @davetosh
  | @copyright HT2 http://ht2.co.uk
  | @license MIT http://opensource.org/licenses/MIT
  |
  |-----------------------------------------------------------------------------------
 */

namespace app\locker\statements;

abstract class xAPIValidationBase implements xAPIValidationInterface
{

    protected $status = 'passed'; // status of the submitted statement. passed or failed.
    protected $errors = array();  // error messages if validation fails
    protected $statement = array();  // the statement submitted
    protected $subStatement = array();
    protected $version = '1.0.0';

    /**
     * {@inheritdoc}
     */
    public function validate($statement = array(), $authority = array())
    {
        throw new \Exception('Implement validate method.');
    }

    public function getSpecificationVersion()
    {
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    public function setStatement($statement)
    {
        $this->statement = $statement;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatement()
    {
        return $this->statement;
    }

    /**
     * Returns true if an array is associative.
     *
     * @param  Array  $arr
     * @return boolean
     */
    protected function isAssoc($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Set errors and status
     *
     * Used to set the statement status and any errors.
     *
     * @param  string  $fail_error   The string to push into the errors array
     * @param  string  $fail_status  The string to set the status to
     */
    private function setError($fail_error = 'There was an error', $fail_status = 'failed')
    {
        $this->status = $fail_status;
        $this->errors[] = $fail_error;
    }

    /**
     * Assertion Checker
     * Checks if an assertion is true
     * Sets a status (default 'failed') and pushed an error on failure/false
     *
     * @param  boolean $assertion    The boolean we are testing
     * @param  string  $fail_error   The string to push into the errors array
     * @param  string  $fail_status  The string to set the status to
     * @return boolean               Whether we the assertion passed the test
     */
    protected function assertionCheck($assertion, $fail_error = 'There was an error', $fail_status = 'failed')
    {
        if (!$assertion) {
            $this->setError($fail_error . ' ', $fail_status);
            return false;
        }

        return true;
    }

    /**
     * Generate unique UUID
     *
     * @return UUID
     *
     */
    protected function makeUUID()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Validate submitted keys vs allowed keys.
     *
     * @param $submitted_keys (array) The array of keys to validate
     * @param $valid_keys     (array) The array of valid keys to check against.
     * @return boolean
     */
    protected function checkKeys($valid_keys, $submitted_keys, $section = '')
    {
        $valid = true;
        foreach ($submitted_keys as $k) {
            if (!$this->assertionCheck(in_array($k, $valid_keys), sprintf("`%s` is not a permitted key in %s", $k, $section)))
                $valid = false;
        }

        return $valid;
    }

    /**
     * Used to validate keys and values.
     *
     * Example:
     *
     *  $this->checkParams(
     *      array(
     *          // typed => uuid, required => false
     *          'id'    => array('uuid', false),
     *          // actor: type => array, required -> true
     *          'actor' => array('array', true),
     *          // type => boolean, required => true, allowed value: true/false
     *          'status' => array('boolean', true, array(true, false)),
     *      ),
     *      $input = array(
     *          // array to be validated
     *      ),
     *      $section = '…'
     *  );
     *
     * @param  array  $requirements  A list of allowed parameters with type, required and allowed values, if applicable.
     *                               format: string, boolean, array
     * @param  array  $input         The data being submitted.
     * @param  string $section       The current section of the statement.
     * @return boolean
     */
    protected function checkParams($requirements = array(), $input = array(), $section = '')
    {
        if (empty($input)) {
            return false;
        }

        // first check to see if the data contains invalid keys
        $check_keys = array_diff_key($input, $requirements);

        // if there are foreign keys, set required error message
        if (!empty($check_keys)) {
            foreach ($check_keys as $k => $v) {
                $msg = sprintf("`%s` is not a permitted property in %s", $k, $section);
                $this->setError($msg, $fail_status = 'failed', $value = '');
            }
            return false;
        }

        // there is nothing wrong yet
        $valid = true;

        // loop through all permitted keys and check type, required and values
        foreach ($requirements as $key => $requirement) {
            if (false === $this->checkParam($requirement, isset($input[$key]) ? $input[$key] : null, $key, $section)) {
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * Details for checkParam() method.
     */
    protected function checkParam($requirement, $input, $key, $section) {
        list($type, $required, $allowed_values) = $requirement + array(null, false, array());

        if (!isset($input)) {
            $msg = sprintf("`%s` is a required key and is not present in %s", $key, $section);
            return $this->assertionCheck(!$required, $msg);
        }

        // check data value is not null apart from in extensions
        if ($key != 'extensions') {
            $msg = sprintf("`%s` in '%s' contains a NULL value which is not permitted.", $key, $section);
            if (!$this->assertionCheck(!is_null($input), $msg)) {
                $valid = false;
            }
        }

        $this->checkTypes($key, $input, $type, $section);

        if (!empty($allowed_values) && !in_array($input, $allowed_values)) {
            return false;
        }
    }
}
