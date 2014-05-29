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
        $cors = new CORSCompatible();
        $statement = $cors->Compatible($statement);
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

        if (!isset($statement['version'])) {
            $statement['version'] = $this->getSpecificationVersion();
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
    public function setError($fail_error = 'There was an error', $fail_status = 'failed')
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
     * @todo Replace 'emptyArray' to 'optionalArray'
     * @param mixed   $data   The data to check
     * @param string  $expected_type The type to check for e.g. array, object,
     * @param string  $section The current section being validated. Used in error messages.
     */
    protected function checkTypes($k, $v, $expected_type, $section)
    {
        switch ($expected_type) {
            case 'string':
                $msg = sprintf("`%s` is not a valid string in " . $section, $k);
                $this->assertionCheck(is_string($v), $msg);
                break;
            case 'array':
                // used when an array is required
                $msg = sprintf("`%s` is not a valid array in " . $section, $k);
                $this->assertionCheck(is_array($v) && !empty($v), $msg);
                break;
            case 'emptyArray':
                // used if value can be empty but if available needs to be an array
                if ($v != '') {
                    $msg = sprintf("`%s` is not a valid array in " . $section, $k);
                    $this->assertionCheck(is_array($v), $msg);
                }
                break;
            case 'object':
                $msg = sprintf("`%s` is not a valid object in " . $section, $k);
                $this->assertionCheck(is_object($v), $msg);
                break;
            case 'iri':
                $msg = sprintf("`%s` is not a valid IRI in " . $section, $k);
                $this->assertionCheck($this->validateIRI($v), $msg);
                break;
            case 'iso8601Duration':
                $msg = sprintf("`%s` is not a valid iso8601 Duration format in " . $section, $k);
                $this->assertionCheck($this->validateISO8601($v), $msg);
                break;
            case 'timestamp':
                $msg = sprintf("`%s` is not a valid timestamp in " . $section, $k);
                $this->assertionCheck($this->validateTimestamp($v), $msg);
                break;
            case 'uuid':
                $msg = sprintf("`%s` is not a valid UUID in " . $section, $k);
                $this->assertionCheck($this->validateUUID($v), $msg);
                break;
            case 'irl':
                $msg = sprintf("`%s` is not a valid irl in " . $section, $k);
                $this->assertionCheck((!filter_var($v, FILTER_VALIDATE_URL)), $msg);
                break;
            case 'lang_map':
                $msg = sprintf("`%s` is not a valid language map in " . $section, $k);
                $this->assertionCheck($this->validateLanguageMap($v), $msg);
                break;
            case 'base64':
                $msg = sprintf("`%s` is not a valid string with bas64 contents in " . $section, $k);
                $this->assertionCheck(base64_encode(base64_decode($v)) === $v, $msg);
                break;
            case 'boolean':
                $msg = sprintf("`%s` is not a valid boolean " . $section, $k);
                $this->assertionCheck(is_bool($v), $msg);
                break;
            case 'score':
                $msg = sprintf(" `%s` needs to be a number in " . $section, $k);
                $this->assertionCheck(!is_string($v) && (is_int($v) || is_float($v)), $msg);
                break;
            case 'numeric':
                $msg = sprintf("`%s` is not numeric in " . $section, $k);
                $this->assertionCheck(is_numeric($v), $msg);
                break;
            case 'int':
                $msg = sprintf("`%s` is not a valid number in " . $section, $k);
                $this->assertionCheck(is_int($v), $msg);
                break;
            case 'integer':
                $msg = sprintf("`%s` is not a valid integer in " . $section, $k);
                $this->assertionCheck(is_integer($v), $msg);
                break;
            case 'contentType':
                $msg = sprintf("`%s` is not a valid Internet Media Type in " . $section, $k);
                $this->assertionCheck($this->validateInternetMediaType($v), $msg);
                break;
            case 'mailto':
                $mbox_format = substr($v, 0, 7);
                $msg = sprintf("`%s` is not in the correct format in " . $section, $k);
                $this->assertionCheck($mbox_format === 'mailto:' && is_string($v), $msg);
                break;
        }
    }

    /**
     * @todo Regex to validate Internet media type
     */
    protected function validateInternetMediaType($v)
    {
        return is_string($v);
    }

    /**
     * Details for checkParam() method.
     */
    protected function checkParam($requirement, $input, $key, $section)
    {
        list($type, $required, $allowed_values) = $requirement + array(null, false, array());

        if (is_null($input)) {
            $msg = sprintf("`%s` is a required key and is not present in %s", $key, $section);
            return $this->assertionCheck(!$required, $msg);
        }

        // check data value is not null apart from in extensions
        if ($key !== 'extensions') {
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

    /**
     * Regex to validate language map.
     * Regex from https://github.com/fugu13/tincanschema/blob/master/tincan.schema.json
     *
     * @param string $item
     * @return boolean
     */
    public function validateLanguageMap($item)
    {
        $pattern = '/^(([a-zA-Z]{2,8}((-[a-zA-Z]{3}){0,3})(-[a-zA-Z]{4})?((-[a-zA-Z]{2})|(-\d{3}))?(-[a-zA-Z\d]{4,8})*(-[a-zA-Z\d](-[a-zA-Z\d]{1,8})+)*)|x(-[a-zA-Z\d]{1,8})+|en-GB-oed|i-ami|i-bnn|i-default|i-enochian|i-hak|i-klingon|i-lux|i-mingo|i-navajo|i-pwn|i-tao|i-tsu|i-tay|sgn-BE-FR|sgn-BE-NL|sgn-CH-DE)$/iu';

        foreach ($item as $k => $v) {
            if (preg_match($pattern, $k)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate if a passed item is a valid UUID
     *
     * @param string $item
     * @return boolean
     */
    protected function validateUUID($item)
    {
        $pattern = '/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/i';
        return preg_match($pattern, $item);
    }

    /**
     * Regex to validate an IRI
     * Regex found here http://stackoverflow.com/questions/161738/what-is-the-best-regular-expression-to-check-if-a-string-is-a-valid-url
     *
     */
    protected function validateIRI($value)
    {
        $pattern = '/^[a-z](?:[-a-z0-9\+\.])*:(?:\/\/(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:])*@)?(?:\[(?:(?:(?:[0-9a-f]{1,4}:){6}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|::(?:[0-9a-f]{1,4}:){5}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){4}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:[0-9a-f]{1,4}:[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){3}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:(?:[0-9a-f]{1,4}:){0,2}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){2}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:(?:[0-9a-f]{1,4}:){0,3}[0-9a-f]{1,4})?::[0-9a-f]{1,4}:(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:(?:[0-9a-f]{1,4}:){0,4}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:(?:[0-9a-f]{1,4}:){0,5}[0-9a-f]{1,4})?::[0-9a-f]{1,4}|(?:(?:[0-9a-f]{1,4}:){0,6}[0-9a-f]{1,4})?::)|v[0-9a-f]+[-a-z0-9\._~!\$&\'\(\)\*\+,;=:]+)\]|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}|(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=@])*)(?::[0-9]*)?(?:\/(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@]))*)*|\/(?:(?:(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@]))+)(?:\/(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@]))*)*)?|(?:(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@]))+)(?:\/(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@]))*)*|(?!(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@])))(?:\?(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@])|[\x{E000}-\x{F8FF}\x{F0000}-\x{FFFFD}|\x{100000}-\x{10FFFD}\/\?])*)?(?:\#(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@])|[\/\?])*)?$/iu';
        return preg_match($pattern, $value);
    }

    /**
     * Validate duration conforms to iso8601
     *
     * @param string $item
     * @return boolean
     */
    public function validateISO8601($item)
    {
        $pattern = '/^P((\d+([\.,]\d+)?Y)?(\d+([\.,]\d+)?M)?(\d+([\.,]\d+)?W)?(\d+([\.,]\d+)?D)?)?(T(\d+([\.,]\d+)?H)?(\d+([\.,]\d+)?M)?(\d+([\.,]\d+)?S)?)?$/i';
        return preg_match($pattern, $item);
    }

    /**
     * Validate timestamp.
     */
    public function validateTimestamp()
    {
        if (isset($this->statement['timestamp'])) {
            $timestamp = $this->statement['timestamp'];
        }
        else {
            return false; //no timestamp set
        }

        // check format using http://www.pelagodesign.com/blog/2009/05/20/iso-8601-date-validation-that-doesnt-suck/
        $pattern = '/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/';
        if (!preg_match($pattern, $timestamp) > 0) {
            $this->setError('Timestamp needs to be in ISO 8601 format.');
            return false;
        }

        return true;
    }

}
