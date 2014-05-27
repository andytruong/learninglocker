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
        return $this->validateStructure() && $this->validateObjectType() && $this->validActorIdentifier() && $this->validateGroup();
    }

    protected function validateStructure()
    {
        $schema = [
            'mbox' => ['mailto'],
            'name' => ['string'],
            'objectType' => ['string'],
            'mbox_sha1sum' => ['string'],
            'openID' => ['irl'],
            'account' => ['array'],
            'member' => ['array', false]
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
        $found_id = false;

        // check functional identifier exists and is valid
        foreach (array_keys($this->actor) as $k) {
            if (in_array($k, ['mbox', 'mbox_sha1sum', 'openID', 'account'])) {
                if ($found_id) {
                    $this->manager->setError('A statement can only set one actor functional identifier.');
                    return false;
                }
                $found_id = true;
            }
        }

        if (!$found_id && !isset($this->actor['objectType'])) {
            $this->manager->setError('A statement must have a valid actor functional identifier.');
        }

        return isset($found_id);
    }

    protected function validateObjectType()
    {
        if (!isset($this->actor['objectType'])) {
            return true;
        }

        // check, if objectType is set, that it is either Group or Agent
        $bun = in_array($this->actor['objectType'], ['Agent', 'Group']);
        $msg = 'The Actor objectType must be Agent or Group.';
        if (!$this->manager->assertionCheck($bun, $msg)) {
            return false;
        }

        return true;
    }

    protected function validateGroup()
    {
        if ($this->actor['objectType'] !== 'Group') {
            return true;
        }

        // Unidentified group so it must have an array containing at least one member
        $bun = isset($this->actor['member']) && is_array($this->actor['member']);
        $msg = 'As Actor objectType is Group, it must contain a members array.';
        if (!$this->manager->assertionCheck($bun, $msg)) {
            return false;
        }

        $return = true;
        foreach ($this->actor['member'] as $i => $member) {
            if (!$this->validateGroupMember($i, $member)) {
                $return = false;
            }
        }
        return $return;
    }

    protected function validateGroupMember($i, $member)
    {
        if (isset($member['objectType']) && $member['objectType'] === 'Group') {
            $this->manager->setError('Invalid object with characteristics of a Group when an Agent was expected.');
            return false;
        }

        $validator = new self($this->manager, $member);
        return $validator->validate();
    }

}
