<?php

namespace app\locker\statements\validators;

use app\locker\statements\xAPIValidation as Manager;

abstract class AccountBaseValidator
{

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var array
     */
    protected $input;

    public function __construct($manager, $input)
    {
        $this->manager = $manager;
        $this->input = $input;
    }

    /**
     * Check to make sure an valid identifier has been included in the statement.
     *
     * @return boolean
     */
    protected function validateID()
    {
        $found_id = false;

        // check functional identifier exists and is valid
        foreach (array_keys($this->input) as $k) {
            if (in_array($k, ['mbox', 'mbox_sha1sum', 'openID', 'account'])) {
                if ($found_id) {
                    $this->manager->setError('A statement can only set one actor functional identifier.');
                    return false;
                }
                $found_id = true;
            }
        }

        if (!$found_id && !isset($this->input['objectType'])) {
            $this->manager->setError('A statement must have a valid actor functional identifier.');
        }

        return isset($found_id);
    }

    protected function validateObjectType()
    {
        if (!isset($this->input['objectType'])) {
            return true;
        }

        // check, if objectType is set, that it is either Group or Agent
        $bun = in_array($this->input['objectType'], ['Agent', 'Group']);
        $msg = 'The Actor objectType must be Agent or Group.';
        return $this->manager->assertionCheck($bun, $msg);
    }

    protected function validateGroup()
    {
        if (!isset($this->input['objectType']) || $this->input['objectType'] !== 'Group') {
            return true;
        }

        // Unidentified group so it must have an array containing at least one member
        $bun = isset($this->input['member']) && is_array($this->input['member']);
        $msg = 'As Actor objectType is Group, it must contain a members array.';
        if (!$this->manager->assertionCheck($bun, $msg)) {
            return false;
        }

        $return = true;
        foreach ($this->input['member'] as $i => $member) {
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

        $validator = new static($this->manager, $member);
        return $validator->validate();
    }
}
