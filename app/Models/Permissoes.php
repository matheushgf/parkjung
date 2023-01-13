<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Scout\Searchable;

class Permissao extends Model
{
    use HasFactory;
    use Searchable;

    protected $table = 'permissoes';
    public $timestamps = false;

    protected $fillable = [
        'funcionalidade'
    ];

    //Colunas requisitadas na listagem
    protected $COLUNAS_LISTAGEM = [
        'funcionalidade'
    ];

    public function getListagem($search) {
        return Permissao::search($search)->paginate(10);
    }

    //Casters
}
