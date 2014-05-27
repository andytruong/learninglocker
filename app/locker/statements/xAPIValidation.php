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

use app\locker\statements\validators\ActorValidator,
    app\locker\statements\validators\ContextValidator,
    app\locker\statements\validators\ObjectValidator,
    app\locker\statements\validators\ResultValidator;

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
            // @see app\locker\statements\validators\ObjectValidator::validateObjectSubStatement()
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
        $keys = ['id', 'authority', 'actor', 'attachments', 'verb', 'object', 'context', 'timestamp', 'result', 'version'];
        foreach ($this->statement as $k => $v) {
            if (in_array($k, $keys)) {
                $this->{'validate' . ucfirst($k)}($v);
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
        $validator = new ActorValidator($this, $actor);
        return $validator->validate();
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
        $pattern = [
            'objectType' => ['string', true],
            'name' => ['string', true],
            'mbox' => ['mailto', true],
        ];
        return $this->checkParams($pattern, $authority, 'authority');
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
        $validator = new ObjectValidator($this, $object);
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
        $validator = new ContextValidator($this, $context);
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
        $validator = new ResultValidator($this, $result);
        return $validator->validate();
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

    /**
     * Validate version.
     *
     * @todo Remove hardcode!
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
     *
     * @requirements https://github.com/adlnet/xAPI-Spec/blob/master/xAPI.md#attachments
     * @param array $attachements
     */
    public function validateAttachments($attachments)
    {
        $schema = [
            'usageType' => ['iri', true],
            'display' => ['lang_map', true],
            'description' => ['lang_map', false],
            'contentType' => ['contentType', false],
            'length' => ['int', true],
            'sha2' => ['base64', true],
            'fileUrl' => ['iri', false]
        ];

        // check all keys are valid
        if ($attachments) {
            foreach ($attachments as $a) {
                $this->checkParams($schema, $a, 'attachment');
            }
        }
    }

    /**
      |---------------------------------------------------------------------------------
      | Various validation functions
      |---------------------------------------------------------------------------------
     */

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
     * @todo Regex to validate Internet media type
     */
    protected function validateInternetMediaType()
    {
        return true;
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
