<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Action;
use Auth;



class RichiestaEmissione extends Notification
{
    use Queueable;

    protected $scad;
    protected $data;
    protected $channel = ['database','mail'];
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($scad,$data,$channel,$toUser)
    {
        $this->scad = $scad;
        $this->data = $data;
        $this->channel = $channel;
        $this->toUser = $toUser;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return $this->channel;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $this->data['urlInfo'] = url(Auth::user()->getIntendedUrl().'/home/convdetails/'.$this->scad->convenzione_id);
        $this->data['urlChiusura'] = url(Auth::user()->getIntendedUrl().'/home/emissione/'.$this->scad->id);
        return (new MailMessage)
            ->cc(Auth::user()->email)
            ->subject('Richiesta emissione')    
            ->markdown('mail.richiesta.emissione',$this->data);
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
            'message' => 'Emessa richiesta di emissione per scadenza n. '.$this->scad->id.' (convenzione n.' .$this->scad->convenzione_id. ')',
            'description' => $this->data['description'],
            'toUser' => $this->toUser->email,
            'subject' => 'Richiesta emissione'
        ];
    }
}
