<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProdutoReceitaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('produto_receitas', function (Blueprint $table) {
            $table->integer('receita_id');
            $table->integer('produto_receita_id');
            $table->string('produto_receita_type');
            $table->boolean('status')->default(true);
            $table->integer('quantidade');
            $table->unique(['receita_id', 'produto_receita_id', 'produto_receita_type'], 'produto_receitas_combinacao_unica');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('produto_receitas');
    }
}
