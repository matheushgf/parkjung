<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Permissao extends Model
{
    use HasFactory, Searchable;

    protected $table = 'permissoes';
    public $timestamps = false;

    protected $fillable = [
        'nome',
        'funcionalidade'
    ];

    //Colunas requisitadas na listagem
    protected $COLUNAS_LISTAGEM = [
        'funcionalidade'
    ];

    public function getListagem($search)
    {
        return Permissao::search($search)->paginate(10);
    }

    public function getFuncionalidades()
    {
        return Permissao::all()->pluck('funcionalidade')->toArray();
    }

    //Casters

    //Relacionamentos
    public function grupos()
    {
        return $this->belongsToMany(Grupo::class)
            ->withTimestamps();
    }
}
