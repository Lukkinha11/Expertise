<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalDepartament extends Model
{
    use HasFactory;

    protected $table = "personal_departament";

    protected $fillable = [
        'employee_id',
        'date',
        'total'
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
            return $employee->companies()->withPivot([
                'date',
                'number_of_employees',
                'number_of_partners',
                'admissions',
                'layoffs',
            ]);
        }

        return null;
    }
}
