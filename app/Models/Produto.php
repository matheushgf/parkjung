<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Scout\Searchable;

class Produto extends Model
{
    use HasFactory;
    use Searchable;

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

    public function getListagem($search) {
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

    public function attributes()
    {
        return [
            'preco' => 'preço',
        ];
    }
}
