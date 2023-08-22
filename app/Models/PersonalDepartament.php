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
        'number_of_employees',
        'number_of_partners',
        'admissions',
        'layoffs',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
