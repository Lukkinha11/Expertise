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
        Schema::create('financial', function (Blueprint $table) {
            $table->engine = "InnoDB";
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->date('accrual_date')->comment('Data de competência');
            $table->date('due_date')->comment('Data de vencimento');
            $table->date('expected_date')->comment('Data prevista');
            $table->enum('description', ['Venda']);
            $table->decimal('original_value',20,2)->comment('Valor original da parcela (R$)');
            $table->string('receipt_form',50)->comment('Forma de recebimento');
            $table->decimal('installment_value',20,2)->comment('Valor recebido da parcela (R$)');
            $table->decimal('interest_realized',20,2)->comment('Juros realizado (R$)');
            $table->decimal('fine_performed',20,2)->comment('Multa realizado (R$)');
            $table->decimal('discont_made',20,2)->comment('Desconto realizado (R$)');
            $table->decimal('total_amount_received_installment',20,2)->comment('Valor total recebido da parcela (R$)');
            $table->decimal('open_installment_value',20,2)->comment('Valor da parcela em aberto (R$)');
            $table->decimal('expected_interest',20,2)->comment('Juros previsto (R$)');
            $table->decimal('expected_fine',20,2)->comment('Multa previsto (R$)');
            $table->decimal('expected_discount',20,2)->comment('Desconto previsto (R$)');
            $table->decimal('total_amount_open_installment',20,2)->comment('Valor total da parcela em aberto (R$)');
            $table->string('bank_account',100)->comment('Conta bancária');
            $table->string('category_1',100)->comment('Categoria 1');
            $table->decimal('value_category_1',20,2)->comment('Valor na Categoria 1');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial');
    }
};
