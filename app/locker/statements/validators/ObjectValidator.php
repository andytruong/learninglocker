<?php

namespace app\locker\statements\validators;

use app\locker\statements\xAPIValidation as Manager;

class ObjectValidator
{

    protected $object;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * Pattern for definition structure.
     *
     * @var arary
     */
    protected $definition_pattern = array(
        'name' => array('lang_map'),
        'description' => array('lang_map'),
        'type' => array('iri'),
        'moreInfo' => array('irl'),
        'extensions' => array('array'),
        'interactionType' => array('string'),
        'correctResponsesPattern' => array('array'),
        'choices' => array('array'),
        'scale' => array('array'),
        'source' => array('array'),
        'target' => array('array'),
        'steps' => array('array')
    );

    public function __construct($manager, $object)
    {
        $this->manager = $manager;
        $this->object = $object;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getObjectPattern($object_type)
    {
        switch ($object_type) {
            case 'SubStatement':
                break;

            case 'StatementRef':
                $pattern += array(
                    'id' => array('uuid', true),
                    'definition' => array('emptyArray')
                );
                break;

            case 'Agent':
                $pattern += array(
                    'name' => array('string'),
                    'mbox' => array('mailto')
                );
                break;

            default:
                $pattern += array(
                    'id' => array('iri', true),
                    'definition' => array('emptyArray')
                );
        }

        return array('objectType' => array('string')) + $pattern;
    }

    public function validate()
    {
        $valid_types = ['Activity', 'Group', 'Agent', 'SubStatement', 'StatementRef'];
        $object_type = isset($this->object['objectType']) ? $this->object['objectType'] : 'Activity';

        if ($this->manager->checkKeys($valid_types, [$object_type], 'object')) {
            switch ($object_type) {
                case 'SubStatement':
                    return $this->validateObjectSubStatement();
                default:
                    return $this->validateObjectBasic();
            }
        }
    }

    protected function validateObjectSubStatement()
    {
        // remove "id", "stored", "version" or "authority" if exist
        unset($this->object['id'], $this->object['stored'], $this->object['version'], $this->object['authority']);

        // check object type is not SubStatement as nesting is not permitted
        if ($this->object['object']['objectType'] == 'SubStatement') {
            $this->setError('A SubStatement cannot contain a nested statement.');
            return false;
        }

        $this->manager->setSubStatement($this->object);
    }

    protected function validateObjectBasic()
    {
        $pattern = $this->getObjectPattern($this->object['objectType']);

        if (!$this->manager->checkParams($pattern, $this->object, 'object')) {
            return false;
        }

        if (isset($this->object['definition'])) {
            if (!$this->manager->checkParams($this->definition_pattern, $this->object['definition'], 'Object Definition')) {
                return false;
            }
            return $this->validateObjectDefinition();
        }
    }

    protected function validateObjectDefinition()
    {
        $def = $this->object['definition'];

        return $this->validateObjectDefinitionInterfactionType()
                && $this->validateObjectDefinitionMisc()
                && $this->validateObjectDefinitionExtension()
            ;
    }

    /**
     * @see https://github.com/adlnet/xAPI-Spec/blob/master/xAPI.md#interaction-components
     */
    protected function validateObjectDefinitionInterfactionType()
    {
        if (isset($this->object['definition']['interactionType'])) {
            $allowed_interaction_types = [
                'choice', 'sequencing', 'likert', 'matching',
                'Performance', 'true-false', 'fill-in', 'numeric', 'other'
            ];

            $bun = in_array($def['interactionType'], $allowed_interaction_types);
            $msg = 'Object: definition: interactionType is not valid.';
            return $this->manager->assertionCheck($bun, $msg);
        }
        return true;
    }

    protected function validateObjectDefinitionMisc()
    {
        $return = true;

        $def = $this->object['definition'];

        if (isset($def['choices'], $def['scale'], $def['source'], $def['target'], $def['steps'])) {
            foreach (array('choices', 'scale', 'source', 'target', 'steps') as $k) {
                // check activity object definition only has valid keys.
                $is_valid = $this->checkKeys(array('id', 'description'), $def[$k], 'Object Definition');

                $msg = 'Object: definition: It has an invalid property.';
                if (!$this->assertionCheck($is_valid, $msg)) {
                    $return = false;
                }

                $bun = isset($def[$k]['id']) && isset($def[$k]['description']);
                $msg = 'Object: definition: It needs to be an array with keys id and description.';
                if (!$this->assertionCheck($bun, $msg)) {
                    $return = false;
                }
            }
        }

        return $return;
    }

    protected function validateObjectDefinitionExtension()
    {
        $bun = !isset($def['extensions']) || is_array($def['extensions']);
        $msg = 'Object: definition: extensions need to be an object.';
        return $this->manager->assertionCheck($bun, $msg);
    }
}
