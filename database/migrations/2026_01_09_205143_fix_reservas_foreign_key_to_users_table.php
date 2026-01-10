<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            // Tentar remover a foreign key existente (pode não existir)
            DB::statement('ALTER TABLE `reservas` DROP FOREIGN KEY `reservas_user_id_foreign`');
        } catch (\Exception $e) {
            // Se não existir, continua
        }
        
        // Criar a foreign key correta que referencia 'users'
        try {
            DB::statement('ALTER TABLE `reservas` ADD CONSTRAINT `reservas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
        } catch (\Exception $e) {
            // Se já existir, não faz nada
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover a foreign key que referencia 'users'
        DB::statement('ALTER TABLE `reservas` DROP FOREIGN KEY IF EXISTS `reservas_user_id_foreign`');
        
        // Recriar a foreign key original que referencia 'users'
        DB::statement('ALTER TABLE `reservas` ADD CONSTRAINT `reservas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
    }
};
