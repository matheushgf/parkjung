<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Scout\Searchable;

class Combo extends Model
{
    use HasFactory, Searchable;

    protected $table = 'combos';
    public $timestamps = true;

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
        return Combo::search($search)->paginate(10);
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
    //Retorna as receitas dentro desta receita
    public function receitas()
    {
        return $this->morphedByMany(Receita::class, 'combo_produto')->withPivot('status', 'quantidade');
    }

    //Retorna os produtos dentro desta receita
    public function produtos()
    {
        return $this->morphedByMany(Produto::class, 'combo_produto')->withPivot('status', 'quantidade');
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

    public function getCombosEditado($termo = '') {
        $query = $this::selectRaw("id, nome as text, 'App.Models.Combo' as tipo");

        if(!empty($termo)){
            $query = $query->where('nome', 'like', '%' . $termo . '%');
        }
        
        return $query->paginate(10);
    }
}
