<?php

namespace App\Listeners;

use Brexis\LaravelWorkflow\Events\GuardEvent;
use Brexis\LaravelWorkflow\Events\LeaveEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Convenzione;
class ConvenzioneWorkflowSubscriber
{
    /**
     * Handle workflow guard events.
     */
    public function onGuard(GuardEvent $event) {
      
        $originalEvent = $event->getOriginalEvent(); 
        $model = $originalEvent->getSubject();
        $permission = $originalEvent->getTransition()->getName().' '.$model->getTable();
        
        if(!Auth::check()){
            $originalEvent->setBlocked(true);
            return;
        }

        if (!Auth::user()->can($permission)){
            $originalEvent->setBlocked(true);            
        }    

    }

    /**
     * Handle workflow leave event.
     */
    public function onLeave(LeaveEvent $event) {

        $originalEvent = $event->getOriginalEvent();        
        $conv  = $originalEvent->getSubject();
        
        if ($conv instanceof Convenzione){
            $conv->logtransitions()->create([
                'transition_leave'=>  $originalEvent->getTransition()->getName(),
                'user_id'=> $conv->user_id
                ]);

            if ($originalEvent->getTransition() != null){
                Log::info('Convenzione (id:'.  $originalEvent->getSubject()->getId() .') esegue transizione '.
                $originalEvent->getTransition()->getName() .
                    ' da '.implode(', ', array_keys( $originalEvent->getMarking()->getPlaces())).
                    ' a '.implode(', ',  $originalEvent->getTransition()->getTos()));
            }
            
        }
    }

    /**
     * Handle workflow transition event.
     */
    public function onTransition($event) {}

    /**
     * Handle workflow enter event.
     */
    public function onEnter($event) {}

    /**
     * Handle workflow entered event.
     */
    public function onEntered($event) {}

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Brexis\LaravelWorkflow\Events\GuardEvent',
            'App\Listeners\ConvenzioneWorkflowSubscriber@onGuard'
        );

        $events->listen(
            'Brexis\LaravelWorkflow\Events\LeaveEvent',
            'App\Listeners\ConvenzioneWorkflowSubscriber@onLeave'
        );

        $events->listen(
            'Brexis\LaravelWorkflow\Events\TransitionEvent',
            'App\Listeners\ConvenzioneWorkflowSubscriber@onTransition'
        );

        $events->listen(
            'Brexis\LaravelWorkflow\Events\EnterEvent',
            'App\Listeners\ConvenzioneWorkflowSubscriber@onEnter'
        );

        $events->listen(
            'Brexis\LaravelWorkflow\Events\EnteredEvent',
            'App\Listeners\ConvenzioneWorkflowSubscriber@onEntered'
        );
    }

}