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
}
