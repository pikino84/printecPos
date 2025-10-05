<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    protected $signature = 'test:email {email}';
    protected $description = 'Enviar email de prueba';

    public function handle()
    {
        $email = $this->argument('email');
        
        try {
            Mail::raw('Este es un email de prueba desde Printec', function($message) use ($email) {
                $message->to($email)
                    ->subject('Prueba de Email - Printec');
            });

            $this->info("Email enviado exitosamente a {$email}");
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}