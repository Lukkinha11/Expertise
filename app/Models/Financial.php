<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Financial extends Model
{
    use HasFactory;

    protected $table = "financial";

    protected $fillable = [
        'company_id',
        'accrual_date',
        'due_date',
        'expected_date',
        'description',
        'original_value',
        'receipt_form',
        'installment_value',
        'interest_realized',
        'fine_performed',
        'discont_made',
        'total_amount_received_installment',
        'open_installment_value',
        'expected_interest',
        'expected_fine',
        'expected_discount',
        'total_amount_open_installment',
        'bank_account',
        'category_1',
        'value_category_1',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
