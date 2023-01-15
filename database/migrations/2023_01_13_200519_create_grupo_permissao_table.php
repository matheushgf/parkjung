<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrupoPermissaoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grupo_permissao', function (Blueprint $table) {
            $table->integer('grupo_id');
            $table->integer('permissao_id');
            $table->primary(['grupo_id', 'permissao_id']);	
            $table->boolean('ler')->default(false);
            $table->boolean('escrever')->default(false);
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
        Schema::dropIfExists('grupo_permissao');
    }
}