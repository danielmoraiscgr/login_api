<?php

namespace App\Listeners;

use App\Events\EventResetarSenha;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Mail\EmailRegistroConfirmacaoResetarEmail;
use Illuminate\Support\Facades\Mail;

class ListenerConfirmacaoResetarEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  EventResetarSenha  $event
     * @return void
     */
    public function handle(EventResetarSenha $event)
    {
        Mail::to($event->user)
        ->send(new EmailRegistroConfirmacaoResetarEmail($event->user));
    }
}
