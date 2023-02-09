<?php

namespace App\Http\Controllers;

use App\Models\EstoqueHistorico;
use Illuminate\Http\Request;
use App\Models\Receita;
use App\Models\Estoque;
use Axiom\Rules\Decimal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EstoquesController extends Controller
{
    public function __construct()
    {
    }
    
    public function index(Request $request)
    {
        $params = $request->all();

        $estoques = (new Estoque())
            ->getListagem(!empty($params['search']) ? $params['search'] : '');

        return view('estoques.list', [
            'estoques' => $estoques,
            'params' => $params,
            'listagem' => true
        ]);
    }

    public function new()
    {
        $user = Auth::user();

        return view('estoques.new', [
            'token' => $user->createToken('receitas_getIngredientes', ['receitas-getIngredientes'])->plainTextToken,
            'tipos' => EstoqueHistorico::TIPOS_OPERACAO_TEXTO
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'estocavel_id' => 'required|integer',
            'estocavel_type' => 'string|nullable',
            'quantidade' => 'required|integer|min:1',
            'tipo' => Rule::in(EstoqueHistorico::TIPOS_OPERACAO),
        ]);

        $params = $request->post();
        $user = Auth::user();
        $estoque = Estoque::where([
            ['estocavel_id', '=', $params['estocavel_id']],
            ['estocavel_type', '=', $params['estocavel_type']]
        ])->first();

        if (!$estoque) {
            $quantidade = 0;

            switch ((int) $params['tipo']) {
                case EstoqueHistorico::TIPO_OPERACAO_ENTRADA:
                    $quantidade += (int) $params['quantidade'];
                    break;
                case EstoqueHistorico::TIPO_OPERACAO_SAIDA:
                    return redirect()->back()
                        ->withErrors(['tipo' => ['A primeira operação de um item não pode ser negativa']])
                        ->withInput();
            }
            
            $estoque = Estoque::create([
                'estocavel_id' => $params['estocavel_id'],
                'estocavel_type' => $params['estocavel_type'],
                'quantidade' => $quantidade,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            $quantidade = $estoque->quantidade;

            switch ((int) $params['tipo']) {
                case EstoqueHistorico::TIPO_OPERACAO_ENTRADA:
                    $quantidade += (int) $params['quantidade'];
                    break;
                case EstoqueHistorico::TIPO_OPERACAO_SAIDA:
                    $quantidade -= (int) $params['quantidade'];
                    break;
            }

            if ($quantidade < 0) {
                return redirect()->back()
                    ->withErrors(['tipo' => ['Saldo de estoque insuficiente para retirada']])
                    ->withInput();
            }

            $estoque->quantidade = $quantidade;
            $estoque->updated_at = now();
            $estoque->save();
        }
        EstoqueHistorico::create(
            [
                'estoque_id' => $estoque->id,
                'user_id' => $user->id,
                'quantidade' => $params['quantidade'],
                'tipo' => (int) $params['tipo'],
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
        
        return redirect()->route('estoques.list')->with('success','Operação de estoque realizada com sucesso.');
    }

    public function historico(Request $request, Estoque $estoque) {
        $params = $request->all();
        $user = Auth::user();

        $historicos = (new EstoqueHistorico())->getListagem(
            $estoque->id,
            !empty($params['user']) ? $params['user'] : null,
            !empty($params['tipo']) ? $params['tipo'] : null,
            !empty($params['data-inicio']) ? $params['data-inicio'] : null,
            !empty($params['data-final']) ? $params['data-final'] : null
        );

        return view('estoques.historico', [
            'historicos' => $historicos,
            'estoque' => $estoque,
            'params' => $params,
            'tokenUsuarios' => $user->createToken('grupos_getUsers', ['grupos-getUsers'])->plainTextToken,
            'tipos' => EstoqueHistorico::TIPOS_OPERACAO_TEXTO,
            'listagem' => true,
            'ignorarNovo' => true,
            'ignorarPesquisa' => true
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
}