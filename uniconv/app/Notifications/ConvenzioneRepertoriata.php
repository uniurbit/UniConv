<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Auth;
use App\Convenzione;


class ConvenzioneRepertoriata extends Notification
{
    use Queueable;

    protected $conv;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($conv)
    {
        $this->conv = $conv;
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
     * @param  App\User $notifiable 
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)          
                    ->bcc(config('unidem.administrator_email'))   
                    ->greeting(' ')                           
                    ->line('La convenzione '.$this->conv->descrizione_titolo.' è stata repertoriata ('.$this->conv->num_rep.')')
                    ->action('Apri convenzione', url(Auth::user()->getIntendedUrl().'/home/convenzioni/'.$this->conv->id))
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
            'model_id' => $this->conv->id,
            'model_type' => get_class($this->conv),
            'message' => 'La convenzione '.$this->conv->descrizione_titolo.' (n. '.$this->conv->id.') è stata repertoriata! ',
            'description' => 'La convenzione '.$this->conv->descrizione_titolo.' è stata repertoriata ('.$this->conv->num_rep.')',
            'subject' => 'Convenzione repertoriata'
        ];
    }
}
