<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Scout\Searchable;

class Estoque extends Model
{
    use HasFactory, Searchable;

    protected $table = 'estoques';
    public $timestamps = true;

    protected $fillable = [
        'estocavel_id',
        'estocavel_type',
        'quantidade'
    ];

    //Colunas requisitadas na listagem
    protected $COLUNAS_LISTAGEM = [
        'estocavel_id',
        'estocavel_type',
        'quantidade'
    ];

    public function getListagem($search)
    {
        return Estoque::whereHasMorph(
            'estocavel',
            [Receita::class, Produto::class],
            function (Builder $query) use($search) {
                $query->where('nome', 'like', '%' . $search . '%');
            }
        )->paginate(10);
    }

    //Casters
    protected function tipoEditado(): Attribute
    {
        return new Attribute(
            get: fn ($value, $attributes) => self::getEstocavelTipoEditado($attributes['estocavel_type']),
        );
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

    //Relacionamentos
    //Retorna o estocavel
    public function estocavel()
    {
        return $this->morphTo();
    }

    public function historicos()
    {
        return $this->hasMany(EstoqueHistorico::class);
    }

    //Atributos
    public function attributes()
    {
        return [
            'preco' => 'preço'
        ];
    }

    public function getEstocavelTipoEditado($tipo)
    {
        switch ($tipo) {
            case 'App\\Models\\Receita':
                return 'Receita';
            case 'App\\Models\\Produto':
                return 'Produto';
            default:
                return 'Não especificado';
        }
    }
}
