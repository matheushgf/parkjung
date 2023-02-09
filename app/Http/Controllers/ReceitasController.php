<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Receita;
use App\Models\Produto;
use Axiom\Rules\Decimal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Eastwest\Json\Json;

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
                'tipo' => 'App.Models.Receita',
                'id' => $receitaIn->id,
                'text' => $receitaIn->nome,
                'quantidade' => $receitaIn->pivot->quantidade
            ];

            $ingredientesObject[] = $ingredientesObjectItem;
        }
        foreach ($receita->produtos()->wherePivot('status', '=', true)->get() as $produto) {
            $ingredientesObjectItem = [
                'tipo' => 'App.Models.Produto',
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
        $arIngredientes = $params['ingredientes'];
        $idReceita = $receita->id;
        $arIngredientesSelecionados = [];
        $arIngredientesAtualizado = [];
        unset($params['ingredientes']);
        
        $ingredientesAtuais = DB::table('produto_receitas')
            ->where([
                ['receita_id', '=', $idReceita]
            ])
            ->get();
        
        foreach($arIngredientes as $ingrediente) {
            $arIngredientesSelecionados[$ingrediente['id'] . '-' . $ingrediente['tipo']] = $ingrediente['quantidade'];
            $criar = true;
            foreach ($ingredientesAtuais as $ingredienteAtual) {
                //Se id e tipo do selecionado sao iguais ao atual
                if (
                    $ingredienteAtual->produto_receita_id == $ingrediente['id']
                    && $ingredienteAtual->produto_receita_type == $ingrediente['tipo']
                ) {
                    $criar = false;
                }
            }
            if ($criar) {
                //Cria linha
                $arIngredientesAtualizado[] = [
                    'receita_id' => $idReceita,
                    'produto_receita_id' => $ingrediente['id'],
                    'produto_receita_type' => $ingrediente['tipo'],
                    'status' => true,
                    'quantidade' => $ingrediente['quantidade'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        foreach($ingredientesAtuais as $ingrediente) {
            $idIngrediente = $ingrediente->produto_receita_id . '-' . $ingrediente->produto_receita_type;
            $flIngredienteSelecionado = array_key_exists(
                $idIngrediente, 
                $arIngredientesSelecionados
            );
            $quantidadeSelecionado = $flIngredienteSelecionado ? $arIngredientesSelecionados[$idIngrediente] : null;
            $ingredienteAtualizado = [
                'receita_id' => $receita->id,
                'produto_receita_id' => $ingrediente->produto_receita_id,
                'produto_receita_type' => $ingrediente->produto_receita_type
            ];
            
            //O ingrediente atual não está nos selecionados, remover
            if (!$flIngredienteSelecionado) {
                $ingredienteAtualizado = array_merge($ingredienteAtualizado, [
                    'status' => false,
                    'quantidade' => $ingrediente->quantidade,
                    'created_at' => $ingrediente->created_at,
                    'updated_at' => now()
                ]);
            }

            //Está nos selecionados e com status false, reativar
            if ($flIngredienteSelecionado && !$ingrediente->status) {
                $ingredienteAtualizado = array_merge($ingredienteAtualizado, [
                    'status' => true,
                    'quantidade' => $quantidadeSelecionado,
                    'created_at' => $ingrediente->created_at,
                    'updated_at' => now()
                ]);
            }

            //Está nos selecionados e com status true, com quantidade alterada, atualizar se necessário
            if (
                $flIngredienteSelecionado
                && $ingrediente->status
            ) {
                if ($ingrediente->quantidade != $quantidadeSelecionado) {
                    $ingredienteAtualizado = array_merge($ingredienteAtualizado, [
                        'status' => true,
                        'quantidade' => $quantidadeSelecionado,
                        'created_at' => $ingrediente->created_at,
                        'updated_at' => now()
                    ]);
                } else {
                    $ingredienteAtualizado = array_merge($ingredienteAtualizado, [
                        'status' => true,
                        'quantidade' => $quantidadeSelecionado,
                        'created_at' => $ingrediente->created_at,
                        'updated_at' => $ingrediente->updated_at
                    ]);
                }
            }

            $arIngredientesAtualizado[] = $ingredienteAtualizado;
        }

        $receita->fill($params)->save();
        DB::table('produto_receitas')->upsert(
            $arIngredientesAtualizado,
            ['receita_id', 'produto_receita_id', 'produto_receita_type'],
            ['status', 'quantidade', 'updated_at']
        );
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

    public function getIngredientes(Request $request)
    {
        $params = $request->all();
        $searchEstocavel = !empty($params['search_estocavel']);

        if ($searchEstocavel) {
            switch ($params['estocavel_type']) {
                case 'App.Models.Receita':
                    return (new Receita())->getReceitasEditado('', $params['estocavel_id'])->first()->toArray();
                case 'App.Models.Produto':
                    return (new Produto())->getProdutosEditado('', $params['estocavel_id'])->first()->toArray();
            }
        }

        $receitas = (new Receita())->getReceitasEditado(!empty($params['search']) ? $params['search'] : '')->toArray();
        $produtos = (new Produto())->getProdutosEditado(!empty($params['search']) ? $params['search'] : '')->toArray();
        $itens = array_merge($receitas['data'], $produtos['data']);

        return $itens;
    }
}