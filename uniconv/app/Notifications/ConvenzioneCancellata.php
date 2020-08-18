<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Auth;

class ConvenzioneCancellata extends Notification
{
    use Queueable;

    protected $conv;
    protected $msg;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($conv, $msg)
    {
        $this->conv = $conv;
        $this->msg = $msg;
    } 

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return null;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'model_id' => $this->conv->id,
            'model_type' => get_class($this->conv),
            'message' => 'La convenzione '.$this->conv->descrizione_titolo.' (n. '.$this->conv->id.') è stata cancellata.',
            'description' => $this->msg,
            'subject' => 'Convenzione cancellata'            
        ];
    }
}
