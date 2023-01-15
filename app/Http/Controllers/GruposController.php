<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grupo;
use App\Models\Permissao;
use App\Models\User;
use App\Models\PersonalAccessToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
        $tokens = $user->tokens();
        $token = $user->createToken('grupos');

        return view('grupos.permissoes', [
            'grupo' => $grupo,
            'permissoes' => Permissao::with('grupos')->get(),
            'listagem' => false,
            'token' => $token
        ]);
    }

    public function storePermissoes(Request $request, Grupo $grupo)
    {
        $grupo_permissoes = [];
        foreach (Permissao::all() as $permissao) {
            $id = $permissao->funcionalidade;
            $grupo_permissoes[] = ['permissao_id' => $permissao->id, 'grupo_id' => $grupo->id, 'ler' => !empty($request->input($id . '_ler')), 'escrever' => !empty($request->input($id . '_escrever'))];
        }

        DB::table('grupo_permissao')->upsert(
            $grupo_permissoes,
            ['permissao_id', 'grupo_id'],
            ['ler', 'escrever']
        );

        return redirect()->route('grupos.permissoes', $grupo->id)->with('success','Permissões salvas com sucesso.');
    }

    public function getUsers(Request $request)
    {
        $params = $request->all();

        $usuarios = User::all();

        return $usuarios;
    }
}
