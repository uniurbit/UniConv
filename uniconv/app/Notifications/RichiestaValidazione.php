<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Action;
use App\User;
use Auth;



class RichiestaValidazione extends Notification
{
    use Queueable;

    protected $conv;
    protected $data;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($conv,$data)
    {
        $this->conv = $conv;
        $this->data = $data;
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
        $this->data['urlInfo'] = url(Auth::user()->getIntendedUrl().'/home/convdetails/'.$this->conv->id);
        $this->data['urlChiusura'] = url(Auth::user()->getIntendedUrl().'/home/validazione/'.$this->conv->id);

        $emailresp = $notifiable->responsabile()->email;       

        return (new MailMessage)
            ->cc([$emailresp, Auth::user()->email]) 
            ->subject('Richiesta approvazione')    
            ->markdown('mail.richiesta.validazione',$this->data);
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
            'message' => 'Richiesta di approvazione per la convenzione '.$this->conv->descrizione_titolo.' (n. '.$this->conv->id.') è stata inviata! ',
            'description' => $this->data['description'] ? $this->data['description'] : '',
            'subject' => 'Richiesta approvazione'
        ];
    }
}
