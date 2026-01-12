<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class TestEmail extends Command
{
    protected $signature = 'test:email {email}';
    protected $description = 'Test email sending';

    public function handle()
    {
        $email = $this->argument('email');
        
        try {
            Mail::raw('Teste de email do Laravel', function ($message) use ($email) {
                $message->to($email)->subject('Teste Email');
            });
            
            $this->info("Email enviado para {$email}");
        } catch (\Exception $e) {
            $this->error("Erro: " . $e->getMessage());
        }
    }
}