<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComboProdutoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('combo_produtos', function (Blueprint $table) {
            $table->integer('combo_id');
            $table->integer('combo_produto_id');
            $table->string('combo_produto_type');
            $table->boolean('status')->default(true);
            $table->integer('quantidade');
            $table->unique(['combo_id', 'combo_produto_id', 'combo_produto_type'], 'combo_produtos_combinacao_unica');
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
        Schema::dropIfExists('combo_produtos');
    }
}
