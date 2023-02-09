<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Scout\Searchable;

class Produto extends Model
{
    use HasFactory, Searchable;

    protected $table = 'produtos';
    public $timestamps = false;

    protected $fillable = [
        'nome',
        'descricao',
        'status',
        'preco'
    ];

    //Colunas requisitadas na listagem
    protected $COLUNAS_LISTAGEM = [
        'nome',
        'descricao',
        'status',
        'preco'
    ];

    public function getListagem($search)
    {
        return Produto::search($search)->paginate(10);
    }

    //Casters
    protected function precoEditado(): Attribute
    {
        return new Attribute(
            get: fn ($value, $attributes) => '$' . number_format($attributes['preco'], 2, ',', '.'),
        );
    }

    protected function statusEditado(): Attribute
    {
        return new Attribute(
            get: fn ($value, $attributes) => $attributes['status'] ? 'Ativo' : 'Inativo',
        );
    }

    //Relacionamentos
    public function receitas()
    {
        return $this->morphToMany(Receita::class, 'produto_receita')->withPivot('status', 'quantidade');
    }

    public function combos()
    {
        return $this->morphToMany(Combo::class, 'combo_produto')->withPivot('status', 'quantidade');
    }

    public function estoque()
    {
        return $this->morphMany(Estoque::class, 'estocavel');
    }
    
    //Atributos
    public function attributes()
    {
        return [
            'preco' => 'preço',
            'descricao' => 'descrição'
        ];
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $array = $this->toArray();
  
        return $array;
    }

    public function getProdutosEditado($termo = '', $id = null) {
        $query = $this::selectRaw("id, nome as text, 'App.Models.Produto' as tipo");

        if(!empty($termo)){
            $query = $query->where('nome', 'like', '%' . $termo . '%');
        }
        if (!empty($id)) {
            $query = $query->where('id', '=', $id);
        }
        
        return $query->paginate(10); 
    }
}
