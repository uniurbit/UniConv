<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Auth;

class ScadenzaEmessa extends Notification
{
    use Queueable;

    protected $scad;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($scad)
    {
        $this->scad = $scad;
    } 

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database','mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)    
                    ->bcc(config('unidem.administrator_email'))
                    ->greeting(' ')                                    
                    ->line('Emesso documento di debito per scadenza n. '.$this->scad->id.' (convenzione n.' .$this->scad->convenzione_id. ')')
                    ->action('Apri la scadenza', url(Auth::user()->getIntendedUrl().'/home/scadenzeview/'.$this->scad->id))
                    ->line("Grazie per usare la nostra applicazione!")
                    ->salutation('Cordiali saluti, '.nl2br('Il team di UniConv'));
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
            'model_id' =>  $this->scad->id,
            'model_type' => get_class($this->scad),
            'message' => 'Emesso documento di debito per scadenza n. '.$this->scad->id.' (convenzione n.' .$this->scad->convenzione_id. ')',
            'description' => 'Emesso documento di debito per scadenza (n. '.$this->scad->id.' (convenzione n.' .$this->scad->convenzione_id. ')',
            'subject' => 'Emissione'
        ];
    }
    
}
