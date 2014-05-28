<?php

namespace app\locker\statements\validators;

class ActorValidator extends AccountBaseValidator
{

    public function validate()
    {
        return $this->validateStructure()
                && $this->validateObjectType()
                && $this->validateID()
                && $this->validateGroup();
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

        return $this->manager->checkParams($schema, $this->input, 'actor');
    }

}
