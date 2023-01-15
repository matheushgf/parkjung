<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use \App\Models\Produto;
use \App\Models\User;
use \App\Models\Permissao;
use \App\Models\Grupo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{   
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $u = new User;
        $u->name = 'admin';
        $u->email = 'admin@admin.com';
        $u->username = 'admin';
        $u->password = Hash::make('Pa$$w0rd');
        $u->force_password_change = false;
        $u->save();

        //Produtos
        foreach(range(1, 12) as $i){
            $pr = new Produto;
            $pr->nome = 'Produto ' . $i;
            $pr->descricao = 'Produto de número ' . $i;
            $pr->preco = $i+($i/100);
            $pr->save();
        }

        //Permissões
        $per = new Permissao;
        $per->nome = 'Produtos';
        $per->funcionalidade = 'produtos';
        $per->save();

        //Permissões
        $per = new Permissao;
        $per->nome = 'Grupos';
        $per->funcionalidade = 'grupos';
        $per->save();

        //Grupos
        $gru = new Grupo;
        $gru->nome = 'superadmin';
        $gru->descricao = 'Grupo de superusuários';
        $gru->save();

        //Grupo_Permissao
        $gp = DB::table('grupo_permissao')->insert([
            'grupo_id' => 1,
            'permissao_id' => 1,
            'ler' => true,
            'escrever' => true
        ]);

        //Grupo_User
        $gu = DB::table('grupo_user')->insert([
            'grupo_id' => 1,
            'user_id' => 1
        ]);
    }
}
