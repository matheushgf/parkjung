<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grupo;
use App\Models\Permissao;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Eastwest\Json\Json;

class GruposController extends Controller
{
    public function __construct()
    {
    }
    
    public function index(Request $request)
    {
        $params = $request->all();

        $grupos = (new Grupo())
            ->getListagem(!empty($params['search']) ? $params['search'] : '');
        
        return view('grupos.list', [
            'grupos' => $grupos,
            'params' => $params,
            'listagem' => true
        ]);
    }

    public function new()
    {
        return view('grupos.new');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|min:4|max:255',
            'descricao' => 'string|nullable'
        ]);

        Grupo::create($request->post());
        return redirect()->route('grupos.list')->with('success','Grupo cadastrado com sucesso.');
    }

    public function edit(Grupo $grupo)
    {
        return view('grupos.edit', compact('grupo'));
    }

    public function update(Request $request, Grupo $grupo)
    {
        $validated = $request->validate([
            'nome' => 'required|min:4|max:255',
            'descricao' => 'string|nullable'
        ]);

        $grupo->fill($request->post())->save();
        return redirect()->route('grupos.list')->with('success','Grupo editado com sucesso.');
    }

    public function delete(Grupo $grupo)
    {
        $grupo->status = false;
        $grupo->save();
        return redirect()->route('grupos.list')->with('success','Grupo inativado com sucesso.');
    }

    public function restore(Grupo $grupo)
    {
        $grupo->status = true;
        $grupo->save();
        return redirect()->route('grupos.list')->with('success','Grupo ativado com sucesso.');
    }

    public function permissoes(Grupo $grupo)
    {
        $user = Auth::user();
        
        $permissoesLer = $grupo->permissoes()
            ->where('ler', true)
            ->pluck('id')
            ->toArray();
        $permissoesEscrever = $grupo->permissoes()
            ->where('escrever', true)
            ->pluck('id')
            ->toArray();
        $usuarios = $grupo->usuarios()
            ->where('status', true)
            ->get();

        $jsonUsuarios = Json::encode($usuarios, JSON_UNESCAPED_SLASHES);

        return view('grupos.permissoes', [
            'grupo' => $grupo,
            'permissoes' => Permissao::with('grupos')->get(),
            'listagem' => false,
            'token' => $user->createToken('grupos_getUsers', ['grupos-getUsers'])->plainTextToken,
            'permissoesLer' => $permissoesLer,
            'permissoesEscrever' => $permissoesEscrever,
            'jsonUsuarios' => $jsonUsuarios
        ]);
    }

    public function storePermissoes(Request $request, Grupo $grupo)
    {
        $usuariosSelecionados = $request->input('users');
        $grupoPermissoes = [];
        $grupoUsuarios = [];

        foreach (Permissao::all() as $permissao) {
            $id = $permissao->funcionalidade;
            $grupoPermissoes[] = ['permissao_id' => $permissao->id, 'grupo_id' => $grupo->id, 'ler' => !empty($request->input($id . '_ler')), 'escrever' => !empty($request->input($id . '_escrever'))];
        }

        foreach ($usuariosSelecionados as $usuario) {
            $grupoUsuarios[] = ['grupo_id' => $grupo->id, 'user_id' => $usuario, 'status' => true, 'updated_at' => now(), 'created_at' => now()];
        }
        
        //Atualiza permissões
        DB::table('grupo_permissao')
            ->upsert(
                $grupoPermissoes,
                ['permissao_id', 'grupo_id'],
                ['ler', 'escrever']
            );

        //Atualiza removidos
        DB::table('grupo_user')
            ->whereNotIn('user_id', $usuariosSelecionados)
            ->where('status', true)
            ->update(
                ['status' => false]
            );
        
        //Atualiza adicionados
        DB::table('grupo_user')
            ->upsert(
                $grupoUsuarios,
                ['user_id', 'grupo_id'],
                ['status', 'updated_at']
            );

        return redirect()->route('grupos.permissoes', $grupo->id)->with('success','Permissões salvas com sucesso.');
    }

    public function getUsers(Request $request)
    {
        $params = $request->all();

        $usuarios = (new User())->getUsersEditado(!empty($params['search']) ? $params['search'] : '');

        return $usuarios;
    }
}
