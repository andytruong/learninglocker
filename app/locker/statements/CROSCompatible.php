<?php

namespace app\locker\statements;

class CORSCompatible extends xAPIValidationBase {

    public function Compatible($statement) {
        foreach ($statement as $type => &$v) {
            switch ($type) {
                case 'actor':
                    // mbox & name must be string.
                    $v['mbox'] = isset($v['mbox']) && is_array($v['mbox']) ? reset($v['mbox']) : $v['mbox'];
                    $v['name'] = isset($v['name']) && is_array($v['name']) ? reset($v['name']) : $v['name'];
                    break;
                case 'verb':
                    // Verb must be array.
                    $v = is_string($v) ? $this->getVerb($v) : $v;
                    break;
                case 'object':
                    // Set default object type is activity.
                    if (!isset($v['objectType'])) {
                        $v['objectType'] = 'Activity';
                    }
                    // Object id must be url.
                    if (isset($v['id']) && !$this->validateIRI($v['id'])) {
                        $v['id'] = "tag:adlnet.gov,2013:expapi:1.0:activities:{$v['id']}";
                    }
                    // object definition type must be IRI.
                    if (isset($v['definition']['type'])) {
                        $v['definition']['type'] = $this->getActivities(strtolower($v['definition']['type']));
                    }
                    break;
                case 'context':
                    if (empty($v['registration'])) {
                        unset($v['registration']);
                    }
                    break;
            }
        }
        return $statement;
    }

    protected function getVerb($name) {
        // List verb.
        $verbs = array(
            'answered', 'asked', 'attempted', 'attended', 'commented', 'completed',
            'exited', 'experienced', 'failed', 'imported', 'initialized', 'interacted',
            'launched', 'mastered', 'passed', 'preferred', 'progressed', 'registered',
            'responded', 'resumed', 'scored', 'shared', 'suspended', 'terminated', 'voided',
        );
        if (in_array($name, $verbs)) {
            return array(
                "id" => "http://adlnet.gov/expapi/verbs/{$name}",
                "display" => array("en-US" => $name)
            );
        }
        return array();
    }

    protected function getActivities($name) {
        $activities = array(
            'course' => 'http://adlnet.gov/expapi/activities/course',
            'module' => 'http://adlnet.gov/expapi/activities/module',
            'meeting' => 'http://adlnet.gov/expapi/activities/meeting',
            'media' => 'http://adlnet.gov/expapi/activities/media',
            'performance' => 'http://adlnet.gov/expapi/activities/performance',
            'simulation' => 'http://adlnet.gov/expapi/activities/simulation',
            'assessment' => 'http://adlnet.gov/expapi/activities/assessment',
            'interaction' => 'http://adlnet.gov/expapi/activities/interaction',
            'cmi.interaction' => 'http://adlnet.gov/expapi/activities/cmi.interaction',
            'question' => 'http://adlnet.gov/expapi/activities/question',
            'objective' => 'http://adlnet.gov/expapi/activities/objective',
            'link' => 'http://adlnet.gov/expapi/activities/link'
        );
        return isset($activities[$name]) ? $activities[$name] : $name;
    }

}
