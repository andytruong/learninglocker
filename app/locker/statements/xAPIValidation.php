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

/**
 * @todo Test sub-statement.
 */
class xAPIValidation extends xAPIValidationBase
{

    /**
     * {@inheritdoc}
     */
    public function validate($statement = array(), $authority = array())
    {
        $this->setStatement($statement, $authority);

        if ($this->validateStructure()) {
            $this->validateParts();

            // now validate a sub statement if one exists
            if (!empty($this->subStatement)) {
                $this->validate($this->subStatement);
            }
        }

        return array('status' => $this->status,
            'errors' => $this->errors,
            'statement' => $this->statement);
    }

    /**
     * General validation of the core properties.
     *
     * @requirements https://github.com/adlnet/xAPI-Spec/blob/master/xAPI.md#dataconstraints
     */
    protected function validateStructure()
    {
        $msg_type = 'The statement doesn\'t exist or is not in the correct format.';
        if ($this->assertionCheck(!empty($this->statement) && is_array($this->statement), $msg_type)) {
            $patterns = array(
                'id' => array('uuid', false),
                'actor' => array('array', true),
                'verb' => array('array', true),
                'object' => array('array', true),
                'result' => array('emptyArray', false),
                'context' => array('emptyArray', false),
                'timestamp' => array('timestamp', false),
                'authority' => array('emptyArray', false),
                'version' => array('string', false),
                'attachments' => array('emptyArray', false)
            );

            return $this->checkParams($patterns, $this->statement, 'core statement');
        }
    }

    /**
     * Validate elements of statement.
     *
     * @return boolean
     */
    protected function validateParts() {
        foreach ($this->statement as $k => $v) {
            switch ($k) {
                case 'id':
                    return $this->validateId($v);
                case 'authority':
                    return $this->validateAuthority($v);
                case 'actor':
                    return $this->validateActor($v);
                case 'verb':
                    return $this->validateVerb($v);
                case 'object':
                    return $this->validateObject($v);
                case 'context':
                    return $this->validateContext($v);
                case 'timestamp':
                    return $this->validateTimestamp($v);
                case 'result':
                    return $this->validateResult($v);
                case 'version':
                    return $this->validateVersion($v);
                case 'attachments':
                    return $this->validateAttachments($v);
            }
        }
    }

    /**
     * Validate statement ID.
     *
     * @param UUID $id The statement ID.
     * @requirements https://github.com/adlnet/xAPI-Spec/blob/master/xAPI.md#stmtid
     */
    protected function validateId($id)
    {
        return $this->checkParams(
            array('statementId' => array('uuid', true)),
            array('statementId' => $id),
            'statementId'
        );
    }

    /**
     * Validate actor. Mandatory.
     *
     * @requirements https://github.com/adlnet/xAPI-Spec/blob/master/xAPI.md#actor
     * @param array $actor
     * @todo check only one functional identifier is passed
     */
    protected function validateActor($actor)
    {
        $actor_valid = $this->checkParams(
            array(
                'mbox' => array('mailto'),
                'name' => array('string'),
                'objectType' => array('string'),
                'mbox_sha1sum' => array('string'),
                'openID' => array('irl'),
                'account' => array('array')
            ), $actor, 'actor'
        );

        if ($actor_valid !== true) {
            return false;
        }

        // Check that only one functional identifier exists and is permitted
        $identifier_valid = $this->validActorIdentifier(array_keys($actor));

        if ($identifier_valid != true) {
            return false;
        }

        // check, if objectType is set, that it is either Group or Agent
        if (isset($actor['objectType'])) {
            if (!$this->assertionCheck(($actor['objectType'] == 'Agent' || $actor['objectType'] == 'Group'), 'The Actor objectType must be Agent or Group.')) {
                return false;
            }

            if ($actor['objectType'] === 'Group') {
                // if objectType Group and no functional identifier: unidentified group
                if ($identifier_valid === false) {
                    // Unidentified group so it must have an array containing at least one member
                    $msg = 'As Actor objectType is Group, it must contain a members array.';
                    if (!$this->assertionCheck((isset($actor['member']) && is_array($actor['member'])), $msg)) {
                        return false;
                    }
                }
            }
        }
    }

    /**
     * Validate authority. Mandatory.
     * Overwrite / Add. This assume basic http authentication for now. See @todo
     *
     * @param array $authority
     * @requirements https://github.com/adlnet/xAPI-Spec/blob/master/xAPI.md#authority
     */
    protected function validateAuthority($authority)
    {
        return $this->checkParam(array(
            'objectType' => array('string', true, array('Agent')),
            'name' => array('string', true),
            'mbox' => array('string', true), // << @todo How to validate email address?
        ), $authority, 'authority');
    }

    /**
     *
     * Validate verb. Mandatory.
     * @requirements https://github.com/adlnet/xAPI-Spec/blob/master/xAPI.md#verb
     *
     * @param array $verb
     *
     */
    protected function validateVerb($verb)
    {
        $this->checkParams(array(
                'id' => array('iri', true),
                'display' => array('lang_map', false)
            ), $verb, 'verb');
    }

    /**
     * Validate object. Mandtory.
     *
     * @requirements https://github.com/adlnet/xAPI-Spec/blob/master/xAPI.md#object
     */
    public function validateObject($object)
    {
        $validator = new validators\ObjectValidator($this, $object);
        return $validator->validate();
    }

    /**
     * Validate context. Optional.
     *
     * @requirements https://github.com/adlnet/xAPI-Spec/blob/master/xAPI.md#context
     * @param array $context
     */
    protected function validateContext($context)
    {
        $validator = new validators\ContextValidator($this, $context);
        return $validator->validate();
    }

    /**
     * Validate result. Optional.
     *
     * @requirements https://github.com/adlnet/xAPI-Spec/blob/master/xAPI.md#result
     * @param array $result
     *
     */
    protected function validateResult($result)
    {
        $valid_keys = array('score' => array('emptyArray', false),
            'success' => array('boolean', false),
            'completion' => array('boolean', false),
            'response' => array('string', false),
            'duration' => array('iso8601Duration', false),
            'extensions' => array('emptyArray', false));

        // check all keys submitted are valid
        $this->checkParams($valid_keys, $result, 'result');

        // now check each part of score if it exists
        if (isset($result['score'])) {
            $valid_score_keys = array('scaled' => array('score'),
                'raw' => array('score'),
                'min' => array('score'),
                'max' => array('score'));

            // check all keys submitted are valid
            $this->checkParams($valid_score_keys, $result['score'], 'result score');

            //now check format of each score key
            if (isset($result['score']['scaled'])) {
                if ($result['score']['scaled'] > 1 || $result['score']['scaled'] < -1) {
                    $this->setError('Result: score: scaled must be between 1 and -1.');
                }
            }
            if (isset($result['score']['max'])) {
                if ($result['score']['max'] < $result['score']['min']) {
                    $this->setError('Result: score: max must be greater than min.');
                }
            }
            if (isset($result['score']['min'])) {
                if (isset($result['score']['max'])) {
                    if ($result['score']['min'] > $result['score']['max']) {
                        $this->setError('Result: score: min must be less than max.');
                    }
                }
            }
            if (isset($result['score']['raw'])) {
                if (isset($result['score']['max']) && isset($result['score']['min'])) {
                    if (($result['score']['raw'] > $result['score']['max']) || ($result['score']['raw'] < $result['score']['min'])) {
                        $this->setError('Result: score: raw must be between max and min.');
                    }
                }
            }
        }
    }

    /**
     * Validate timestamp.
     */
    public function validateTimestamp()
    {
        //does timestamp exist?
        if (isset($this->statement['timestamp'])) {
            $timestamp = $this->statement['timestamp'];
        }
        else {
            return false; //no timestamp set
        }

        // check format using http://www.pelagodesign.com/blog/2009/05/20/iso-8601-date-validation-that-doesnt-suck/
        if (!preg_match('/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/', $timestamp) > 0) {
            $this->setError('Timestamp needs to be in ISO 8601 format.');
            return false;
        }

        return true;
    }

    /**
     * Validate version.
     */
    protected function validateVersion()
    {
        if (isset($this->statement['version'])) {
            $result = $result = substr($this->statement['version'], 0, 4);
            if ($result != '1.0.') {
                $this->setError('The statement has an invalid version.');

                return false;
            }
        }
        else {
            $this->statement['version'] = '1.0.0';
        }

        return true;
    }

    /**
     * Validate attachments. Optional.
     * @requirements https://github.com/adlnet/xAPI-Spec/blob/master/xAPI.md#attachments
     *
     * @param array $attachements
     *
     */
    public function validateAttachments($attachments)
    {
        $valid_attachment_keys = array('usageType' => array('iri', true),
            'display' => array('lang_map', true),
            'description' => array('lang_map', false),
            'contentType' => array('contentType', false),
            'length' => array('int', true),
            'sha2' => array('base64', true),
            'fileUrl' => array('iri', false));

        // check all keys are valid
        if ($attachments) {
            foreach ($attachments as $a) {
                $this->checkParams($valid_attachment_keys, $a, 'attachment');
            }
        }
    }

    /**
     * Check to make sure an valid identifier has been included in the statement.
     *
     * @param $actor_keys (array) The array of actor keys to validate
     * @return boolean
     *
     */
    protected function validActorIdentifier($actor_keys)
    {
        $identifier_valid = false;
        $count = 0;
        $functional_identifiers = array('mbox', 'mbox_sha1sum', 'openID', 'account');

        //check functional identifier exists and is valid
        foreach ($actor_keys as $k) {
            if (in_array($k, $functional_identifiers)) {
                $identifier_valid = true;
                $count++; //increment counter so we can check only one identifier is present
            }
        }

        // only allow one identifier
        if ($count > 1) {
            $identifier_valid = false;
            $this->setError('A statement can only set one actor functional identifier.');
        }

        if (!$identifier_valid) {
            $this->setError('A statement must have a valid actor functional identifier.');
        }

        return $identifier_valid;
    }

    /**
     * Check types submitted to ensure allowed
     *
     * @param mixed   $data   The data to check
     * @param string    $expected_type The type to check for e.g. array, object,
     * @param string $section The current section being validated. Used in error messages.
     *
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

    /*
      |---------------------------------------------------------------------------------
      | Various validation functions
      |---------------------------------------------------------------------------------
      |
     */

    /**
     *
     * Regex to validate an IRI
     * Regex found here http://stackoverflow.com/questions/161738/what-is-the-best-regular-expression-to-check-if-a-string-is-a-valid-url
     *
     */
    protected function validateIRI($value)
    {
        if (preg_match('/^[a-z](?:[-a-z0-9\+\.])*:(?:\/\/(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:])*@)?(?:\[(?:(?:(?:[0-9a-f]{1,4}:){6}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|::(?:[0-9a-f]{1,4}:){5}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){4}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:[0-9a-f]{1,4}:[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){3}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:(?:[0-9a-f]{1,4}:){0,2}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:){2}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:(?:[0-9a-f]{1,4}:){0,3}[0-9a-f]{1,4})?::[0-9a-f]{1,4}:(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:(?:[0-9a-f]{1,4}:){0,4}[0-9a-f]{1,4})?::(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})|(?:(?:[0-9a-f]{1,4}:){0,5}[0-9a-f]{1,4})?::[0-9a-f]{1,4}|(?:(?:[0-9a-f]{1,4}:){0,6}[0-9a-f]{1,4})?::)|v[0-9a-f]+[-a-z0-9\._~!\$&\'\(\)\*\+,;=:]+)\]|(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(?:\.(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}|(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=@])*)(?::[0-9]*)?(?:\/(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@]))*)*|\/(?:(?:(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@]))+)(?:\/(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@]))*)*)?|(?:(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@]))+)(?:\/(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@]))*)*|(?!(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@])))(?:\?(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@])|[\x{E000}-\x{F8FF}\x{F0000}-\x{FFFFD}|\x{100000}-\x{10FFFD}\/\?])*)?(?:\#(?:(?:%[0-9a-f][0-9a-f]|[-a-z0-9\._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!\$&\'\(\)\*\+,;=:@])|[\/\?])*)?$/iu', $value)) {
            return true;
        }

        return false;
    }

    /**
     *
     * Regex to validate Internet media type
     *
     */
    private function validateInternetMediaType()
    {
        return true;
    }

    /**
     * Regex to validate language map.
     * Regex from https://github.com/fugu13/tincanschema/blob/master/tincan.schema.json
     *
     * @param string $item
     * @return boolean
     *
     */
    public function validateLanguageMap($item)
    {
        foreach ($item as $k => $v) {
            if (preg_match('/^(([a-zA-Z]{2,8}((-[a-zA-Z]{3}){0,3})(-[a-zA-Z]{4})?((-[a-zA-Z]{2})|(-\d{3}))?(-[a-zA-Z\d]{4,8})*(-[a-zA-Z\d](-[a-zA-Z\d]{1,8})+)*)|x(-[a-zA-Z\d]{1,8})+|en-GB-oed|i-ami|i-bnn|i-default|i-enochian|i-hak|i-klingon|i-lux|i-mingo|i-navajo|i-pwn|i-tao|i-tsu|i-tay|sgn-BE-FR|sgn-BE-NL|sgn-CH-DE)$/iu', $k)) {
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
        if (preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/i', $item)) {
            return true;
        }

        return false;
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

}
