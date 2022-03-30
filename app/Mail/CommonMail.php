<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CommonMail extends Mailable
{
  use Queueable, SerializesModels;

//
  public $user;
  public $view;
  public $subject;
  public $data;
  public $file;
  public $name;



  public function __construct($user=null,$view,$subject='Ride4Health',$data='',$file='',$name='')
  { 
   $this->user =  $user ; 
   $this->view =  $view ;  
   $this->subject =  $subject ;  
   $this->data =  $data ;  
   $this->file =  $file ;
   $this->name =  $name ;

 }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
     if ($this->file) {
       return $this->view($this->view)->subject($this->subject)
       ->attachData($this->file, $this->name, [
        'mime' => 'application/pdf',
      ]);
     }
     return $this->view($this->view)->subject($this->subject);
   }

 }
