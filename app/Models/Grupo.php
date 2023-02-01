<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Grupo extends Model
{
    use HasFactory, Searchable;

    protected $table = 'grupos';
    public $timestamps = true;

    protected $fillable = [
        'nome',
        'descricao',
        'status'
    ];

    //Colunas requisitadas na listagem
    protected $COLUNAS_LISTAGEM = [
        'nome',
        'descricao'
    ];

    public function getListagem($search)
    {
        return Grupo::search($search)->paginate(10);
    }

    //Casters
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
            'descricao' => 'descrição',
            'permissoes' => 'permissões'
        ];
    }

    //Relacionamentos
    public function permissoes()
    {
        return $this->belongsToMany(Permissao::class)
            ->withTimestamps();
    }

    public function usuarios()
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }
}
