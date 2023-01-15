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
        //TODO: melhorar
        // $produtos = Produto::paginate(10);
        // return $produtos->paginate(10);

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
}
