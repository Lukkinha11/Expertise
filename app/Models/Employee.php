<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = "employees";

    protected $fillable = [
        'name',
        'email',
        'departament',
    ];

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'employees_companies', 'employee_id', 'company_id');
    }

    public function supervisions()
    {
        return $this->hasMany(Supervision::class);
    }

    public function accountings()
    {
        return $this->hasMany(Accounting::class);
    }

    public function personalDepartaments()
    {
        return $this->hasMany(PersonalDepartament::class);
    }

    public static function getDepartmentOptions()
    {
        return [
            'Departamento Pessoal' => 'Departamento Pessoal',
            'Fiscal' => 'Fiscal',
            'Contábil' => 'Contábil',
            'Financeiro' => 'Financeiro',
        ];
    }

    // Novo método para acessar o relacionamento de companies como employeeCompanies
    public function employeeCompanies()
    {
        return $this->companies();
    }
}
