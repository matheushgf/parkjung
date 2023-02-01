<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produto;
use Axiom\Rules\Decimal;

class ProdutosController extends Controller
{
    public function __construct()
    {
    }
    
    public function index(Request $request)
    {
        $params = $request->all();

        $produtos = (new Produto())
            ->getListagem(!empty($params['search']) ? $params['search'] : '');

        return view('produtos.list', [
            'produtos' => $produtos,
            'params' => $params,
            'listagem' => true
        ]);
    }

    public function new()
    {
        return view('produtos.new');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|min:3|max:255',
            'descricao' => 'string|nullable',
            'preco' => ['required', new Decimal(2, 2)],
        ]);

        Produto::create($request->post());
        return redirect()->route('produtos.list')->with('success','Produto cadastrado com sucesso.');
    }

    public function edit(Produto $produto)
    {
        return view('produtos.edit', compact('produto'));
    }

    public function update(Request $request, Produto $produto)
    {
        $validated = $request->validate([
            'nome' => 'required|min:4|max:255',
            'descricao' => 'string|nullable',
            'preco' => ['required', new Decimal(2, 2)],
        ]);

        $produto->fill($request->post())->save();
        return redirect()->route('produtos.list')->with('success','Produto editado com sucesso.');
    }

    public function delete(Produto $produto)
    {
        $produto->status = false;
        $produto->save();
        return redirect()->route('produtos.list')->with('success','Produto inativado com sucesso.');
    }

    public function restore(Produto $produto)
    {
        $produto->status = true;
        $produto->save();
        return redirect()->route('produtos.list')->with('success','Produto ativado com sucesso.');
    }

    public function getProdutos(Request $request)
    {
        $params = $request->all();

        $usuarios = (new Produto())->getProdutosEditado(!empty($params['search']) ? $params['search'] : '');

        return $usuarios;
    }
}
