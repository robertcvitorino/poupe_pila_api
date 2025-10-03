<?php

use App\StatusEnum;
use App\TipoEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lista_origem_id')->nullable()->constrained('compras')->onDelete('set null');
            $table->string('nome', 150);
            $table->text('descricao')->nullable();
            $table->enum('tipo', TipoEnum::values())->default(TipoEnum::Lista->value);
            $table->enum('status', StatusEnum::values())->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->string('local', 200)->nullable();
            $table->timestamp('data_compra')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
