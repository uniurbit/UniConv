<?php

namespace App\Observers;

use Auth;
use App\UsersLogs;

class UserActionsObserver
{

    public function saved($model)
    {
        if ($model->wasRecentlyCreated == true) {
            $action = 'created';
        } else {
            $action = 'updated';
        }
        if (Auth::check()) {
            UsersLogs::create([
                'user_id'      => Auth::user()->id,
                'action'       => $action,
                'action_model' => $model->getTable(),
                'action_id'    => $model->id
            ]);
        }
    }

    public function deleting($model)
    {
        if (Auth::check()) {
            UsersLogs::create([
                'user_id'      => Auth::user()->id,
                'action'       => 'deleted',
                'action_model' => $model->getTable(),
                'action_id'    => $model->id
            ]);
        }
    }
}