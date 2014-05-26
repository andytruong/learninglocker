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
    public function setStatement($statement, $authority = array())
    {
        if ($statement = $this->fillMissingElements($statement, $authority)) {
            $this->statement = $statement;
        }

        if (isset($this->statement['stored'])) {
            unset($this->statement['stored']);
        }
    }

    public function setSubStatement($sub_statement)
    {
        $this->subStatement = $sub_statement;
    }

    protected function fillMissingElements($statement, $authority)
    {
        if (!isset($statement['id'])) {
            $statement['id'] = $this->makeUUID();
        }

        // Spec: The LRS MUST ensure that all Statements stored have an authority.
        if (!empty($authority)) {
            $statement['authority'] = array(
                'objectType' => 'Agent',
                'name' => !empty($authority['name']) ? $authority['name'] : 'Aduro',
                'mbox' => 'mailto:' . (!empty($authority['email']) ? $authority['email'] : 'info@aduro.com'),
            );
        }

        return $statement;
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
    public function assertionCheck($assertion, $fail_error = 'There was an error', $fail_status = 'failed')
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
     * @param array $valid_keys     The array of valid keys to check against.
     * @param array $submitted_keys The array of keys to validate
     * @return boolean
     */
    public function checkKeys($valid_keys, $submitted_keys, $section = '')
    {
        $valid = true;

        foreach ($submitted_keys as $k) {
            $msg = sprintf("`%s` is not a permitted key in %s", $k, $section);
            if (!$this->assertionCheck(in_array($k, $valid_keys), $msg)) {
                $valid = false;
            }
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
    public function checkParams($requirements = array(), $input = array(), $section = '')
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
     * Check types submitted to ensure allowed
     *
     * @param mixed   $data   The data to check
     * @param string    $expected_type The type to check for e.g. array, object,
     * @param string $section The current section being validated. Used in error messages.
     */
    protected function checkTypes($key, $value, $expected_type, $section)
    {
        switch ($expected_type) {
            case 'string':
                $this->assertionCheck(is_String($value), sprintf("`%s` is not a valid string in " . $section, $key));
                break;
            case 'array':
                //used when an array is required
                $this->assertionCheck((is_array($value) && !empty($value)), sprintf("`%s` is not a valid array in " . $section, $key));
                break;
            case 'emptyArray':
                //used if value can be empty but if available needs to be an array
                if ($value != '') {
                    $this->assertionCheck(is_array($value), sprintf("`%s` is not a valid array in " . $section, $key));
                }
                break;
            case 'object':
                $this->assertionCheck(is_object($value), sprintf("`%s` is not a valid object in " . $section, $key));
                break;
            case 'iri':
                $this->assertionCheck($this->validateIRI($value), sprintf("`%s` is not a valid IRI in " . $section, $key));
                break;
            case 'iso8601Duration':
                $this->assertionCheck($this->validateISO8601($value), sprintf("`%s` is not a valid iso8601 Duration format in " . $section, $key));
                break;
            case 'timestamp':
                $this->assertionCheck($this->validateTimestamp($value), sprintf("`%s` is not a valid timestamp in " . $section, $key));
                break;
            case 'uuid':
                $this->assertionCheck($this->validateUUID($value), sprintf("`%s` is not a valid UUID in " . $section, $key));
                break;
            case 'irl':
                $this->assertionCheck((!filter_var($value, FILTER_VALIDATE_URL)), sprintf("`%s` is not a valid irl in " . $section, $key));
                break;
            case 'lang_map':
                $this->assertionCheck($this->validateLanguageMap($value), sprintf("`%s` is not a valid language map in " . $section, $key));
                break;
            case 'base64':
                $this->assertionCheck(base64_encode(base64_decode($value)) === $value, sprintf("`%s` is not a valid language map in " . $section, $key));
                break;
            case 'boolean':
                $this->assertionCheck(is_bool($value), sprintf("`%s` is not a valid boolean " . $section, $key));
                break;
            case 'score':
                $this->assertionCheck(!is_string($value) && (is_int($value) || is_float($value)), sprintf(" `%s` needs to be a number in " . $section, $key));
                break;
            case 'numeric':
                $this->assertionCheck(is_numeric($value), sprintf("`%s` is not numeric in " . $section, $key));
                break;
            case 'int':
                $this->assertionCheck(is_int($value), sprintf("`%s` is not a valid number in " . $section, $key));
                break;
            case 'integer':
                $this->assertionCheck(is_integer($value), sprintf("`%s` is not a valid integer in " . $section, $key));
                break;
            case 'contentType':
                $this->assertionCheck($this->validateInternetMediaType($value), sprintf("`%s` is not a valid Internet Media Type in " . $section, $key));
                break;
            case 'mailto':
                $mbox_format = substr($value, 0, 7);
                $this->assertionCheck($mbox_format == 'mailto:' && is_string($value), sprintf("`%s` is not in the correct format in " . $section, $key));
                break;
        }
    }

    /**
     * Details for checkParam() method.
     */
    protected function checkParam($requirement, $input, $key, $section)
    {
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
