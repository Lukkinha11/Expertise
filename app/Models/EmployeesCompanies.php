<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EmployeesCompanies extends Pivot
{
    use HasFactory;

    protected $table = "employees_companies";

    protected $fillable = [
        'employee_id',
        'company_id',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
    
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function accounting(): BelongsTo
    {
        return $this->belongsTo(Accounting::class);
    }
}
