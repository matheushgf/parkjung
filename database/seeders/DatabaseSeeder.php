<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use \App\Models\Produto;
use \App\Models\User;
use Illuminate\Support\Facades\Hash;

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
        $per = new Permissoes;
    }
}
