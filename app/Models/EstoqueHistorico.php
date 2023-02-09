<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Scout\Searchable;
use Carbon\Carbon;

class EstoqueHistorico extends Model
{
    use HasFactory, Searchable;

    protected $table = 'estoques_historico';
    public $timestamps = true;

    protected $fillable = [
        'estoque_id',
        'user_id',
        'quantidade',
        'tipo',
        'status'
    ];

    //Colunas requisitadas na listagem
    protected $COLUNAS_LISTAGEM = [
        'estoque_id',
        'user_id',
        'quantidade',
        'tipo',
        'status'
    ];

    //Constantes
    const TIPO_OPERACAO_ENTRADA = 1;
    const TIPO_OPERACAO_SAIDA = 2;
    const TIPOS_OPERACAO = [
        self::TIPO_OPERACAO_ENTRADA,
        self::TIPO_OPERACAO_SAIDA
    ];
    const TIPOS_OPERACAO_TEXTO = [
        self::TIPO_OPERACAO_ENTRADA => 'Entrada',
        self::TIPO_OPERACAO_SAIDA => 'Saída'
    ];

    public function getListagem(
        $idEstoque,
        $user = null,
        $tipo = null,
        $dataInicio = null,
        $dataFim = null
    )
    {
        // return EstoqueHistorico::where('estoque_id', '=', $idEstoque)
        //     ->orderBy('id', 'desc')
        //     ->paginate(10);

        $query = EstoqueHistorico::where('estoque_id', '=', $idEstoque);

        if (!empty($user)) {
            $query = $query->where('user_id', '=', $user);
        }
        if (!empty($tipo)) {
            $query = $query->where('tipo', '=', $tipo);
        }
        if (!empty($dataInicio)) {
            $dataInicio = Carbon::createFromFormat('Y-m-d', $dataInicio)->startOfDay();
            $query = $query->whereDate('created_at', '>=', $dataInicio);
        }
        if (!empty($dataFim)) {
            $dataFim = Carbon::createFromFormat('Y-m-d', $dataFim)->endOfDay();
            $query = $query->whereDate('created_at', '<=', $dataFim);
        }
        
        return $query->orderBy('id', 'desc')
            ->paginate(10); 
    }
    
    //Casters
    protected function tipoEditado(): Attribute
    {
        return new Attribute(
            get: fn ($value, $attributes) => self::TIPOS_OPERACAO_TEXTO[(int) $attributes['tipo']],
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
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function estoque()
    {
        return $this->belongsTo(Estoque::class);
    }
}
