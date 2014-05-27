<?php

namespace app\locker\statements\validators;

use app\locker\statements\xAPIValidation as Manager;

class ActorValidator
{

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var array
     */
    protected $actor;

    public function __construct($manager, $actor)
    {
        $this->manager = $manager;
        $this->actor = $actor;
    }

    public function validate()
    {
        return $this->validateStructure()
                    && $this->validateObjectType()
                    && $this->validActorIdentifier()
                    && $this->validateObjectTypeGroup();
    }

    protected function validateStructure()
    {
        $schema = [
            'mbox' => ['mailto'],
            'name' => ['string'],
            'objectType' => ['string'],
            'mbox_sha1sum' => ['string'],
            'openID' => ['irl'],
            'account' => ['array']
        ];

        return $this->manager->checkParams($schema, $this->actor, 'actor');
    }

    /**
     * Check to make sure an valid identifier has been included in the statement.
     *
     * @return boolean
     */
    protected function validActorIdentifier()
    {
        $actor_keys = array_keys($this->actor);

        $valid = false;
        $count = 0;
        $functional_identifiers = ['mbox', 'mbox_sha1sum', 'openID', 'account'];

        // check functional identifier exists and is valid
        foreach ($actor_keys as $k) {
            if (in_array($k, $functional_identifiers)) {
                $valid = true;
                $count++; //increment counter so we can check only one identifier is present
            }
        }

        // only allow one identifier
        if ($count > 1) {
            $valid = false;
            $this->manager->setError('A statement can only set one actor functional identifier.');
        }

        if (!$valid) {
            $this->manager->setError('A statement must have a valid actor functional identifier.');
        }

        return $valid;
    }

    protected function validateObjectType()
    {
        if (!isset($this->actor['objectType'])) {
            $this->manager->setError('objectType property was required to be a string but was absent.');
            return false;
        }

        // check, if objectType is set, that it is either Group or Agent
        $bun = in_array($this->actor['objectType'], ['Agent', 'Group']);
        $msg = 'The Actor objectType must be Agent or Group.';
        if (!$this->manager->assertionCheck($bun, $msg)) {
            return false;
        }

        return true;
    }

    protected function validateX()
    {
        if ($this->actor['objectType'] !== 'Group') {
            return true;
        }

        // Unidentified group so it must have an array containing at least one member
        $bun = isset($this->actor['member']) && is_array($this->actor['member']);
        $msg = 'As Actor objectType is Group, it must contain a members array.';
        if (!$this->assertionCheck($bun, $msg)) {
            return false;
        }
    }

}
