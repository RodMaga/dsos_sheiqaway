<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('trip_id');
            $table->string('ticket_code')->nullable();
            $table->string('status')->default('confirmado');
            $table->string('passenger_name');
            $table->string('booking_reference')->nullable();
            $table->decimal('price', 8, 2);
            $table->timestamp('purchase_date')->useCurrent();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
    public function down() {
        Schema::dropIfExists('reservas');
    }
};
