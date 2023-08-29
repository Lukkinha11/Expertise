<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Accounting extends Model
{
    use HasFactory;

    protected $table = "accounting";

    protected $fillable = [
        'employee_id',
        'date',
    ];

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
