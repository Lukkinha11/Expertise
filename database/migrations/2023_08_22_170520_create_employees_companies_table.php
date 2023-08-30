<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees_companies', function (Blueprint $table) {
            $table->engine = "InnoDB";
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('company_id');
            $table->string('date',8)->nullable()->comment('Data Y/m');
            $table->integer('number_of_employees')->nullable()->comment('Quantidade de Funcionários');
            $table->integer('number_of_partners')->nullable()->comment('Quantidade de Sócios');
            $table->integer('admissions')->nullable()->comment('Quantidade Admissões');
            $table->integer('layoffs')->nullable()->comment('Quantidade de demissões');
            
            $table->foreign('employee_id')->references('id')->on('employees')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees_companies');
    }
};
