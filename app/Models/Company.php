<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $table = "companies";

    protected $fillable = [
        'code',
        'branch',
        'company_name',
        'cnpj',
    ];

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employees_companies', 'employee_id', 'company_id');
    }

    public function financials()
    {
        return $this->hasMany(Financial::class);
    }
}
