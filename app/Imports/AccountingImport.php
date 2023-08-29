<?php

namespace App\Imports;

use App\Models\Accounting;
use App\Models\Company;
use App\Models\Employee;
use Exception;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use function App\Helpers\format_document;

class AccountingImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithValidation
{
    use Importable;
    /**
    * @param Collection $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public $exceptionTransation; // variavel responsavel por retornar exceptions ao efetuar cadastros no banco

    public function collection(Collection $rows)
    {
        try {
            DB::transaction(function () use ($rows) {
                foreach ($rows as $row) {
                    if ($this->isValidRow($row)) {

                        $employee = Employee::firstOrCreate([
                            'name' => $row['resp_contabil']
                        ], [
                            'name' => $row['resp_contabil'],
                            'email' => "DEFINIR_EMAIL@TESTE.COM",
                            'departament' => 'Contábil',
                        ]);

                        $company = Company::firstOrCreate([
                            'cnpj' => format_document($row['cnpj'])
                        ], [
                            'company_name' => $row['nome_empresa'],
                            'cnpj' => format_document($row['cnpj']),
                            'code' => $row['empresa'],
                            'branch' => $row['filial'],
                        ]);

                        Accounting::firstOrCreate([
                            'employee_id' => $employee->id,
                            'date' => substr($row['anomes'], 0, 4) . '/' . substr($row['anomes'], 4),
                        ], [
                            'employee_id' => $employee->id,
                            'date' => substr($row['anomes'], 0, 4) . '/' . substr($row['anomes'], 4),
                        ]);

                        $employee->companies()->syncWithoutDetaching([$company->id => ['date' => substr($row['anomes'], 0, 4) . '/' . substr($row['anomes'], 4)]]);

                    }
                }
            });

        } catch (Exception $e) {

            return $this->exceptionTransation = $e;
        }
    }

    /**
     * Validação customizada para verificar se o funcionário já existe no banco de dados
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            
            foreach ($validator->getData() as $key => $data) {

                if (!isset($data['empresa']) || !isset($data['filial']) || !isset($data['nome_empresa']) || !isset($data['cnpj']) || !isset($data['resp_contabil']) || !isset($data['anomes'])) {
                    $validator->errors()->add($key,'É obrigatório a nomeação das colunas na planilha!');

                } else {
                    $employee = Employee::where('name', $data['resp_contabil'])->first();

                    if ($employee) {
                        $supervasion = Accounting::where('date',substr($data['anomes'], 0, 4) . '/' . substr($data['anomes'], 4))->where('employee_id', $employee->id)->exists();

                        if ($supervasion) {
                            $validator->errors()->add($key, 'O Resp. Contábil: ' . $data['resp_contabil'] .' já está cadastrado!');
                        }
                    }
                }
            }
        });
    }

    public function rules(): array 
    {
        return [
            '*.empresa' => [
                'required',
                'integer',
            ],
            '*.filial' => [
                'required',
                'integer',
            ],
            '*.nome_empresa' => [
                'required',
                'string',
                'min:3'
            ],
            '*.cnpj' => [
                'required',
                'cpf_ou_cnpj'
            ],
            '*.resp_contabil' => [
                'required',
                'string',
                'min:3'
            ],
            '*.anomes' => [
                'required',
                'min:6',
                'max:6'
            ],
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'empresa.required' => 'A coluna Empresa na planilha é obrigatória.',
            'empresa.integer' => 'A coluna Empresa na planilha deve ser um número inteiro.',
            'filial.required' => 'A coluna Email na planilha é obrigatória.',
            'filial.integer' => 'A coluna Filial na planilha deve ser um número inteiro.',
            'nome_empresa.required' => 'A coluna Nome Empresa na planilha é obrigatória.',
            'nome_empresa.string' => 'A coluna Nome Empresa na planilha deve ser uma string.',
            'nome_empresa.min' => 'A coluna Nome Empresa na planilha deve ter no mínimo 3 caracteres.',
            'cnpj.required' => 'O CNPJ na planilha é obrigatória.',
            'cnpj.cnpj' => 'A coluna CNPJ na planilha deve ser um CNPJ válido.',
            'resp_contabil.required' => 'A coluna Resp. Contábil na planilha é obrigatória.',
            'resp_contabil.string' => 'A coluna Resp. Contábil na planilha deve ser uma string.',
            'resp_contabil.min' => 'A coluna Resp. Contábil na planilha deve ter no mínimo 3 caracteres.',
            'anomes.required' => 'A coluna Ano/Mês na planilha é obrigatória.',
            'anomes.string' => 'A coluna Ano/Mês na planilha deve ser uma string.',
            'anomes.min' => 'A coluna Ano/Mês na planilha deve ter no mínimo 6 caracteres.',
            'anomes.max' => 'A coluna Ano/Mês na planilha deve ter no máximo 6 caracteres.',
        ];
    }

    /**
     * Verifica se a linha contém valores preenchidos.
     *
     * @param $row
     *
     * @return bool
     */
    private function isValidRow($row): bool
    {
        // Verifica se pelo menos um valor na linha está preenchido
        foreach ($row as $value) {
            if (!empty($value)) {
                return true;
            }
        }

        return false;
    }
}
