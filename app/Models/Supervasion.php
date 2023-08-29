<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supervasion extends Model
{
    use HasFactory;

    protected $table = "supervasion";

    protected $fillable = [
        'employee_id',
        'date',
    ];

    protected $dates = ['date'];

    /**
     * Funções estão comentadas para caso seja preciso eu usar no futuro na geração dos graficos
     */

    // public function setDateAttribute($value) //Função responsável por trocar a / por - para salvar no banco de dados
    // {
    //     $this->attributes['date'] = str_replace('/', '-', $value);
    // }

    // public function getDateAttribute($value) //Função responsável por trocar a - por / para mostrar para o usuário
    // {
    //     return $this->attributes['date'] = str_replace('-', '/', $value);
    // }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function companies()
    {
        // Acesse o Employee associado a esta entrada de contabilidade
        $employee = $this->employee;

        // Verifique se o Employee está carregado antes de acessar as empresas
        if ($employee) {
            return $employee->companies();
        }

        return null;
    }
}
