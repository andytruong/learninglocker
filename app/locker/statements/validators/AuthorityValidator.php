<?php

namespace app\locker\statements\validators;

use app\locker\statements\xAPIValidation as Manager;

class AuthorityValidator extends AccountBaseValidator
{

    /**
     * @var Manager
     */
    protected $manager;

    public function validate()
    {
        return $this->validateStructure()
                && $this->validateObjectType()
                && $this->validateID()
                && $this->validateGroup();
    }

    public function validateStructure()
    {
        $pattern = [
            'objectType' => ['string', true],
            'name' => ['string'],
            'mbox' => ['mailto'],
            'member' => ['array'],
        ];

        return $this->manager->checkParams($pattern, $this->input, 'authority');
    }

}
