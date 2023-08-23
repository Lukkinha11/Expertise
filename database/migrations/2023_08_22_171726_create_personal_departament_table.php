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
        Schema::create('personal_departament', function (Blueprint $table) {
            $table->engine = "InnoDB";
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->integer('number_of_employees')->comment('Quantidade de Funcionários');
            $table->integer('number_of_partners')->comment('Quantidade de Sócios');
            $table->integer('admissions')->comment('Quantidade Admissões');
            $table->integer('layoffs')->comment('Quantidade de demissões');

            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_departament');
    }
};