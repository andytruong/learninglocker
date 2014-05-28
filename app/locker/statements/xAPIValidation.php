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
    app\locker\statements\validators\AuthorityValidator,
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
            // Validate elements of statement.
            $keys = ['id', 'authority', 'actor', 'attachments', 'verb', 'object', 'context', 'timestamp', 'result', 'version'];
            foreach ($this->statement as $k => $v) {
                if (in_array($k, $keys)) {
                    $this->{'validate' . ucfirst($k)}($v);
                }
            }

            // now validate a sub statement if one exists
            // @see app\locker\statements\validators\ObjectValidator::validateObjectSubStatement()
            if (!empty($this->subStatement)) {
                $this->validate($this->subStatement);
            }
        }

        return ['status' => $this->status, 'errors' => $this->errors, 'statement' => $this->statement];
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
        $validator = new AuthorityValidator($this, $authority);
        return $validator->validate();
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
        $pattern = [
            'id' => ['iri', true],
            'display' => ['array', true]
        ];

        return $this->checkParams($pattern, $verb, 'verb');
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
            'display' => ['emptyArray', false],
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

}
