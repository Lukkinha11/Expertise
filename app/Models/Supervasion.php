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

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
