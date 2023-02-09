<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use \App\Models\Produto;
use \App\Models\User;
use \App\Models\Permissao;
use \App\Models\Grupo;
use \App\Models\Receita;
use \App\Models\Combo;
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
        $u->created_at = now();
        $u->save();

        $u = new User;
        $u->name = 'Arthur Bernardi';
        $u->email = 'matheushgf.ferreira@gmail.com';
        $u->username = 'arthur';
        $u->password = Hash::make('Pa$$w0rd');
        $u->force_password_change = false;
        $u->created_at = now();
        $u->save();

        //Produtos
        $pr = new Produto;
        $pr->nome = 'Sashimi';
        $pr->preco = 20;
        $pr->save();

        $pr = new Produto;
        $pr->nome = 'Nori';
        $pr->preco = 20;
        $pr->save();

        $pr = new Produto;
        $pr->nome = 'Arroz Cru';
        $pr->preco = 20;
        $pr->save();

        $pr = new Produto;
        $pr->nome = 'Água';
        $pr->preco = 20;
        $pr->fora_estoque = true;
        $pr->save();

        $pr = new Produto;
        $pr->nome = 'Açúcar';
        $pr->preco = 20;
        $pr->save();

        $pr = new Produto;
        $pr->nome = 'Sal';
        $pr->preco = 20;
        $pr->save();

        //Permissões
        $per = new Permissao;
        $per->nome = 'Produtos';
        $per->funcionalidade = 'produtos';
        $per->save();

        $per = new Permissao;
        $per->nome = 'Grupos';
        $per->funcionalidade = 'grupos';
        $per->save();

        $per = new Permissao;
        $per->nome = 'Receitas';
        $per->funcionalidade = 'receitas';
        $per->save();

        //Grupos
        $gru = new Grupo;
        $gru->nome = 'superadmin';
        $gru->descricao = 'Grupo de superusuários';
        $gru->save();

        //Grupo_Permissao
        DB::table('grupo_permissao')->insert([
            'grupo_id' => 1,
            'permissao_id' => 1,
            'ler' => true,
            'escrever' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        //Grupo_User
        DB::table('grupo_user')->insert([
            'grupo_id' => 1,
            'user_id' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        //Receitas
        $rec = new Receita;
        $rec->nome = 'Temaki';
        $rec->descricao = 'Temaki convencional';
        $rec->gerado = 8;
        $rec->preco = 70.00;
        $rec->save();

        $rec = new Receita;
        $rec->nome = 'Arroz Cozido';
        $rec->descricao = 'Arroz já cozido';
        $rec->gerado = 5;
        $rec->save();
        
        //Receita_Produto
        DB::table('produto_receitas')->insert([
            [
                'receita_id' => 2,
                'produto_receita_id' => 3,
                'produto_receita_type' => 'App\Models\Produto',
                'quantidade' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'receita_id' => 2,
                'produto_receita_id' => 4,
                'produto_receita_type' => 'App\Models\Produto',
                'quantidade' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'receita_id' => 2,
                'produto_receita_id' => 5,
                'produto_receita_type' => 'App\Models\Produto',
                'quantidade' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'receita_id' => 2,
                'produto_receita_id' => 6,
                'produto_receita_type' => 'App\Models\Produto',
                'quantidade' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        DB::table('produto_receitas')->insert([
            [
                'receita_id' => 1,
                'produto_receita_id' => 1,
                'produto_receita_type' => 'App\Models\Produto',
                'quantidade' => 16,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'receita_id' => 1,
                'produto_receita_id' => 2,
                'produto_receita_type' => 'App\Models\Produto',
                'quantidade' => 16,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'receita_id' => 1,
                'produto_receita_id' => 2,
                'produto_receita_type' => 'App\Models\Receita',
                'quantidade' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        //Combos
        $com = new Combo;
        $com->nome = 'Jung Dad';
        $com->preco = 1200.00;
        $com->save();
    }
}
