<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Receita;
use App\Models\Produto;
use Axiom\Rules\Decimal;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Eastwest\Json\Json;
use Illuminate\Support\Arr;

class ReceitasController extends Controller
{
    public function __construct()
    {
    }
    
    public function index(Request $request)
    {
        $params = $request->all();

        $receitas = (new Receita())
            ->getListagem(!empty($params['search']) ? $params['search'] : '');

        return view('receitas.list', [
            'receitas' => $receitas,
            'params' => $params,
            'listagem' => true
        ]);
    }

    public function new()
    {
        return view('receitas.new');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|min:3|max:255',
            'descricao' => 'string|nullable',
            'gerado' => 'required|integer|min:1',
            'preco' => [new Decimal(2, 2), 'nullable']
        ]);

        Receita::create($request->post());
        return redirect()->route('receitas.list')->with('success','Receita cadastrada com sucesso.');
    }

    public function edit(Receita $receita)
    {
        $user = Auth::user();
        
        $ingredientesObject = [];
        foreach ($receita->receitas()->wherePivot('status', '=', true)->get() as $receitaIn) {
            $ingredientesObjectItem = [
                'type' => 'App.Models.Receita',
                'id' => $receitaIn->id,
                'text' => $receitaIn->nome,
                'quantidade' => $receitaIn->pivot->quantidade
            ];

            $ingredientesObject[] = $ingredientesObjectItem;
        }
        foreach ($receita->produtos()->wherePivot('status', '=', true)->get() as $produto) {
            $ingredientesObjectItem = [
                'type' => 'App.Models.Produto',
                'id' => $produto->id,
                'text' => $produto->nome,
                'quantidade' => $produto->pivot->quantidade
            ];

            $ingredientesObject[] = $ingredientesObjectItem;
        }
        $jsonIngredientes = Json::encode($ingredientesObject, JSON_UNESCAPED_SLASHES);

        return view('receitas.edit', [
            'receita' => $receita,
            'token' => $user->createToken('receitas_getIngredientes', ['receitas-getIngredientes'])->plainTextToken,
            'ingredientes' => $ingredientesObject,
            'jsonIngredientes' => $jsonIngredientes
        ]);
    }

    public function update(Request $request, Receita $receita)
    {
        $validated = $request->validate([
            'nome' => 'required|min:4|max:255',
            'descricao' => 'string|nullable',
            'gerado' => 'required|integer|min:1',
            'preco' => [new Decimal(2, 2), 'nullable'],
            'ingredientes.*.quantidade' => 'required'
        ]);

        $params = $request->post();
        $arIngredientesTipo = [];
        $arIngredientes = $params['ingredientes'];
        unset($params['ingredientes']);

        foreach($arIngredientes as $ingrediente) {
            if (!array_key_exists($ingrediente['tipo'], $arIngredientesTipo)) {
                $arIngredientesTipo[$ingrediente['tipo']] = [];
            }
            $arIngredientesTipo[$ingrediente['tipo']][] = $ingrediente['id'];
        }

        $receita->fill($params)->save();

        if (array_key_exists('App\\Models\\Receita', $arIngredientesTipo)) {
            //Pega receitas removidas
            $receitasRemovidas = $receita->receitas()
                ->whereNotIn('produto_receita_id', $arIngredientesTipo['App\\Models\\Receita'])
                ->pluck('produto_receita_id')
                ->toArray();
            //Atualiza status e campo updated_at
            $receita->receitas()
                ->updateExistingPivot($receitasRemovidas, ['status' => false, 'updated_at' => now()]);

            //Pega receitas já existentes
            $idsReceitasExistentes = $receita->receitas()
                ->whereIn('produto_receita_id', $arIngredientesTipo['App\\Models\\Receita'])
                ->pluck('produto_receita_id')
                ->toArray();
            //Tira da lista os existentes, ficando apenas os novos, e adiciona
            $idsReceitasNovas = Arr::except($arIngredientesTipo['App\\Models\\Receita'], $idsReceitasExistentes);
            foreach ($arIngredientes as $ingrediente) {
                if (in_array($ingrediente['id'], $idsReceitasNovas)) {
                    $receita->receitas()->attach($ingrediente['id'], ['status' => 1, 'quantidade' => $ingrediente['quantidade'], 'created_at' => now(), 'updated_at' => now()]);
                }
            }
        }
        if (array_key_exists('App\\Models\\Produto', $arIngredientesTipo)) {
            //Pega produtos removidos
            $produtosRemovidos = $receita->produtos()
                ->whereNotIn('produto_receita_id', $arIngredientesTipo['App\\Models\\Produto']) //3, 4, 5, 2
                ->wherePivot('status', '=', true)
                ->pluck('produto_receita_id')
                ->toArray();
            //Atualiza status e campo updated_at
            $receita->produtos()
                ->updateExistingPivot($produtosRemovidos, ['status' => false, 'updated_at' => now()]);

            $produtosReadicionar = $receita->produtos()
                ->whereIn('produto_receita_id', $arIngredientesTipo['App\\Models\\Produto'])
                ->wherePivot('status', '=', false)
                ->pluck('produto_receita_id')
                ->toArray();
            //Atualiza status de produtos existentes na tabela mas com status false
            foreach ($arIngredientes as $ingrediente) {
                if (in_array($ingrediente['id'], $produtosReadicionar)) {
                    $receita->produtos()
                        ->updateExistingPivot($ingrediente['id'], ['status' => true, 'quantidade' => $ingrediente['quantidade'], 'updated_at' => now()]);
                }
            }
            
            //Pega produtos já existentes
            $idsProdutosExistentes = $receita->produtos()
            ->whereIn('produto_receita_id', $arIngredientesTipo['App\\Models\\Produto'])
            ->wherePivot('status', '=', true)
            ->pluck('produto_receita_id')
            ->toArray();

            //Tira da lista os existentes, ficando apenas os novos, e adiciona
            $idsProdutosNovos = array_diff($arIngredientesTipo['App\\Models\\Produto'], $idsProdutosExistentes);
            foreach ($arIngredientes as $ingrediente) {
                if (in_array($ingrediente['id'], $idsProdutosNovos)) {
                    $receita->produtos()->attach($ingrediente['id'], ['status' => true, 'quantidade' => $ingrediente['quantidade'], 'created_at' => now(), 'updated_at' => now()]);
                }
            }
        }

        // // $arIngredientesRem = $arIngredientesRemovidos->get();
        // echo '<pre>';
        // var_dump($arIngredientesRemovidos->get());
        // echo '</pre><br><br>';
        // return 'Sucesso';
        return redirect()->route('receitas.list')->with('success','Receita editada com sucesso.');
    }

    public function delete(Receita $receita)
    {
        $receita->status = false;
        $receita->save();
        return redirect()->route('receitas.list')->with('success','Receita inativada com sucesso.');
    }

    public function restore(Receita $receita)
    {
        $receita->status = true;
        $receita->save();
        return redirect()->route('receitas.list')->with('success','Receita ativada com sucesso.');
    }

    public function getReceitas(Request $request)
    {
        $params = $request->all();

        $usuarios = (new Receita())->getReceitasEditado(!empty($params['search']) ? $params['search'] : '');

        return $usuarios;
    }
}