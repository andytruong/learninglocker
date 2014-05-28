<?php

namespace app\locker\statements\validators;

use app\locker\statements\xAPIValidation as Manager;

/**
 * @todo Validate team, language
 * @todo The revision property MUST only be used if the Statement's Object is an Activity.
 * @todo The platform property MUST only be used if the Statement's Object is an Activity.
 * @todo The language property MUST NOT be used if not applicable or unknown.
 */
class ContextValidator
{

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var array
     */
    protected $context;

    public function __construct($manager, $context)
    {
        $this->manager = $manager;
        $this->context = $context;
    }

    public function validate()
    {
        return $this->validateStructure() && $this->validateActivities();
    }

    protected function validateStructure()
    {
        $pattern = [
            'registration' => ['uuid', false],
            'instructor' => ['emptyArray', false],
            'team' => ['emptyArray', false],
            'contextActivities' => ['emptyArray', false],
            'revision' => ['string', false],
            'platform' => ['string', false],
            'language' => ['string', false], // @string defined in RFC-5646
            'statement' => ['uuid', false],
            'extensions' => ['emptyArray', false],
        ];

        $this->manager->checkParams($pattern, $this->context, 'context');
    }

    /**
     * Check properties in contextActivies
     */
    protected function validateActivities()
    {
        if (!isset($this->context['contextActivities'])) {
            return true;
        }

        $activities = &$this->context['contextActivities'];
        $statement = $this->manager->getStatement();

        $valid_context_keys = array(
            'parent' => array('array'),
            'grouping' => array('array'),
            'category' => array('array'),
            'other' => array('array')
        );

        // check all keys submitted are valid
        $this->checkParams($valid_context_keys, $activities, 'contextActivities');

        // Now check all property keys contain an array. While the contextActivity
        // may be an object on input, it must be stored as an array - so on each
        // type we will check if an associative array has been passed and insert
        // it into an array if needed.
        foreach (['parent', 'grouping', 'category', 'other'] as $key) {
            if (isset($activities[$key])) {
                if ($this->manager->isAssoc($activities[$key])) {
                    $statement['context']['contextActivities'][$key] = array($activities[$key]);
                }
            }
        }

        $this->manager->setStatement($statement);
    }
}
