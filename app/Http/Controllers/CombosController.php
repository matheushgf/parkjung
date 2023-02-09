<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Receita;
use App\Models\Combo;
use Axiom\Rules\Decimal;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Eastwest\Json\Json;

class CombosController extends Controller
{
    public function __construct()
    {
    }
    
    public function index(Request $request)
    {
        $params = $request->all();

        $combos = (new Combo())
            ->getListagem(!empty($params['search']) ? $params['search'] : '');

        return view('combos.list', [
            'combos' => $combos,
            'params' => $params,
            'listagem' => true
        ]);
    }

    public function new()
    {
        return view('combos.new');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|min:3|max:255',
            'descricao' => 'string|nullable',
            'preco' => ['required', new Decimal(2, 2)]
        ]);

        Combo::create($request->post());
        return redirect()->route('combos.list')->with('success','Combo cadastrado com sucesso.');
    }

    public function edit(Combo $combo)
    {
        $user = Auth::user();
        
        $ingredientesObject = [];
        foreach ($combo->receitas()->wherePivot('status', '=', true)->get() as $receita) {
            $ingredientesObjectItem = [
                'tipo' => 'App.Models.Receita',
                'id' => $receita->id,
                'text' => $receita->nome,
                'quantidade' => $receita->pivot->quantidade
            ];

            $ingredientesObject[] = $ingredientesObjectItem;
        }
        foreach ($combo->produtos()->wherePivot('status', '=', true)->get() as $produto) {
            $ingredientesObjectItem = [
                'tipo' => 'App.Models.Produto',
                'id' => $produto->id,
                'text' => $produto->nome,
                'quantidade' => $produto->pivot->quantidade
            ];

            $ingredientesObject[] = $ingredientesObjectItem;
        }
        $jsonIngredientes = Json::encode($ingredientesObject, JSON_UNESCAPED_SLASHES);

        return view('combos.edit', [
            'combo' => $combo,
            'token' => $user->createToken('receitas_getIngredientes', ['receitas-getIngredientes'])->plainTextToken,
            'ingredientes' => $ingredientesObject,
            'jsonIngredientes' => $jsonIngredientes
        ]);
    }

    public function update(Request $request, Combo $combo)
    {
        $validated = $request->validate([
            'nome' => 'required|min:4|max:255',
            'descricao' => 'string|nullable',
            'preco' => ['required', new Decimal(2, 2)],
            'ingredientes.*.quantidade' => 'required'
        ]);

        $params = $request->post();
        $arIngredientes = $params['ingredientes'];
        $idCombo = $combo->id;
        $arIngredientesSelecionados = [];
        $arIngredientesAtualizado = [];
        unset($params['ingredientes']);
        
        $ingredientesAtuais = DB::table('combo_produtos')
            ->where([
                ['combo_id', '=', $idCombo]
            ])
            ->get();
        
        foreach($arIngredientes as $ingrediente) {
            $arIngredientesSelecionados[$ingrediente['id'] . '-' . $ingrediente['tipo']] = $ingrediente['quantidade'];
            $criar = true;
            foreach ($ingredientesAtuais as $ingredienteAtual) {
                //Se id e tipo do selecionado sao iguais ao atual
                if (
                    $ingredienteAtual->combo_produto_id == $ingrediente['id']
                    && $ingredienteAtual->combo_produto_type == $ingrediente['tipo']
                ) {
                    $criar = false;
                }
            }
            if ($criar) {
                //Cria linha
                $arIngredientesAtualizado[] = [
                    'combo_id' => $idCombo,
                    'combo_produto_id' => $ingrediente['id'],
                    'combo_produto_type' => $ingrediente['tipo'],
                    'status' => true,
                    'quantidade' => $ingrediente['quantidade'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        foreach($ingredientesAtuais as $ingrediente) {
            $idIngrediente = $ingrediente->combo_produto_id . '-' . $ingrediente->combo_produto_type;
            $flIngredienteSelecionado = array_key_exists(
                $idIngrediente, 
                $arIngredientesSelecionados
            );
            $quantidadeSelecionado = $flIngredienteSelecionado ? $arIngredientesSelecionados[$idIngrediente] : null;
            $ingredienteAtualizado = [
                'combo_id' => $combo->id,
                'combo_produto_id' => $ingrediente->combo_produto_id,
                'combo_produto_type' => $ingrediente->combo_produto_type
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

        $combo->fill($params)->save();
        DB::table('combo_produtos')->upsert(
            $arIngredientesAtualizado,
            ['combo_id', 'combo_produto_id', 'combo_produto_type'],
            ['status', 'quantidade', 'updated_at']
        );
        return redirect()->route('combos.list')->with('success','Combo editado com sucesso.');
    }

    public function delete(Combo $combo)
    {
        $combo->status = false;
        $combo->save();
        return redirect()->route('combos.list')->with('success','Combo inativado com sucesso.');
    }

    public function restore(Combo $combo)
    {
        $combo->status = true;
        $combo->save();
        return redirect()->route('combos.list')->with('success','Combo ativado com sucesso.');
    }
}