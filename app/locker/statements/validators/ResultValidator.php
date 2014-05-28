<?php

namespace app\locker\statements\validators;

use app\locker\statements\xAPIValidation as Manager;

class ResultValidator
{

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var array
     */
    protected $result;

    public function __construct($manager, $result)
    {
        $this->manager = $manager;
        $this->result = $result;
    }

    public function validate()
    {
        return $this->validateStructure() && $this->validateScoreStructure() && $this->validateScore();
    }

    protected function validateStructure()
    {
        $valid_keys = [
            'score' => ['emptyArray', false],
            'success' => ['boolean', false],
            'completion' => ['boolean', false],
            'response' => ['string', false],
            'duration' => ['iso8601Duration', false],
            'extensions' => ['emptyArray', false]
        ];

        return $this->manager->checkParams($valid_keys, $this->result, 'result');
    }

    protected function validateScoreStructure()
    {
        if (!isset($this->result['score'])) {
            return true;
        }

        $pattern = [
            'scaled' => array('score'),
            'raw' => array('score'),
            'min' => array('score'),
            'max' => array('score')
        ];

        // check all keys submitted are valid
        $this->manager->checkParams($pattern, $this->result['score'], 'result score');
    }

    protected function validateScore()
    {
        if (!isset($this->result['score'])) {
            return true;
        }

        $return = true;

        // now check format of each score key
        if (isset($this->result['score']['scaled'])) {
            if ($this->result['score']['scaled'] > 1 || $this->result['score']['scaled'] < -1) {
                $this->manager->setError('Result: score: scaled must be between 1 and -1.');
                $return = false;
            }
        }

        if (isset($this->result['score']['max'])) {
            if ($this->result['score']['max'] < $this->result['score']['min']) {
                $this->manager->setError('Result: score: max must be greater than min.');
                $return = false;
            }
        }

        if (isset($this->result['score']['min'])) {
            if (isset($this->result['score']['max'])) {
                if ($this->result['score']['min'] > $this->result['score']['max']) {
                    $this->manager->setError('Result: score: min must be less than max.');
                    $return = false;
                }
            }
        }

        if (isset($this->result['score']['raw'])) {
            if (isset($this->result['score']['max']) && isset($this->result['score']['min'])) {
                if (($this->result['score']['raw'] > $this->result['score']['max']) || ($this->result['score']['raw'] < $this->result['score']['min'])) {
                    $this->manager->setError('Result: score: raw must be between max and min.');
                    $return = false;
                }
            }
        }

        return $return;
    }

}
