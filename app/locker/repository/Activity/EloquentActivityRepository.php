<?php

namespace Locker\Repository\Activity;

use Activity;

class EloquentActivityRepository implements ActivityRepository
{

    /**
     * Activity
     */
    protected $activity;

    /**
     * Construct
     *
     * @param Activity $activity
     */
    public function __construct(Activity $activity)
    {
        $this->activity = $activity;
    }

    public function saveActivity($id, $definition)
    {
        $exists = \Activity::find($id);

        // if the object activity exists, return details on record.
        if ($exists) {
            $exists->definition = $definition;
            $exists->save();
        }
        else {
            $activity = new \Activity();
            $activity->_id = $id;
            $activity->definition = $definition;
            $activity->save();

            // @todo: Why?
            return $definition;
        }
    }

    public function getActivity($id)
    {
        return $this->activity->where('_id', $id)->first();
    }

}
