<?php

namespace App\Imports;

use App\Models\PersonalDepartament;
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

class PersonalDepartamentImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithValidation
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
                            'name' => $row['resp_folha']
                        ], [
                            'name' => $row['resp_folha'],
                            'email' => "DEFINIR_EMAIL@TESTE.COM",
                            'departament' => 'Departamento Pessoal',
                        ]);

                        if ($employee->departament === "Definir Departamento") {
                            $employee->departament = "Departamento Pessoal";
                            $employee->save();
                        }

                        $company = Company::firstOrCreate([
                            'cnpj' => format_document($row['cnpj'])
                        ], [
                            'company_name' => $row['nome_empresa'],
                            'cnpj' => format_document($row['cnpj']),
                            'code' => $row['empresa'],
                            'branch' => $row['filial'],
                        ]);

                        $date = substr($row['anomes'], 0, 4) . '/' . substr($row['anomes'], 4);

                        $personalDepartament = PersonalDepartament::where('employee_id', $employee->id)->where('date', $date)->first();

                        if ($personalDepartament) {

                            $personalDepartament->update([
                                'total' => $personalDepartament->total + $row['qtde_funcionarios'] + $row['qtde_socios'] + $row['admissoes'] + $row['demissoes'],
                            ]);

                        } else {
                            PersonalDepartament::create([
                                'employee_id' => $employee->id,
                                'date' => substr($row['anomes'], 0, 4) . '/' . substr($row['anomes'], 4),
                                'total' => $row['qtde_funcionarios'] + $row['qtde_socios'] + $row['admissoes'] + $row['demissoes'],
                            ]);
                        }

                        $pivotData = [
                            'number_of_employees' => $row['qtde_funcionarios'],
                            'number_of_partners'=> $row['qtde_socios'],
                            'admissions' => $row['admissoes'],
                            'layoffs' => $row['demissoes'],
                            'date' => $date,
                        ];
    
                        $employee->companies()->syncWithoutDetaching([$company->id => $pivotData]);
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

                if (!isset($data['empresa']) || !isset($data['filial']) || !isset($data['nome_empresa']) || !isset($data['cnpj']) || 
                    !isset($data['resp_folha']) || !isset($data['anomes']) || !isset($data['qtde_funcionarios']) || !isset($data['qtde_socios']) || 
                    !isset($data['admissoes']) || !isset($data['demissoes'])) {

                    $validator->errors()->add($key,'É obrigatório a nomeação das colunas na planilha!');

                } else {
                    $employee = Employee::where('name', $data['resp_folha'])->first();

                    if ($employee) {
                        $supervasion = PersonalDepartament::where('date',substr($data['anomes'], 0, 4) . '/' . substr($data['anomes'], 4))->where('employee_id', $employee->id)->exists();

                        if ($supervasion) {
                            $validator->errors()->add($key, 'O Resp. DP: ' . $data['resp_folha'] .' já está cadastrado!');
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
            '*.resp_folha' => [
                'required',
                'string',
                'min:3'
            ],
            '*.anomes' => [
                'required',
                'min:6',
                'max:6'
            ],
            '*.qtde_funcionarios' => [
                'required',
                'integer',
            ],
            '*.qtde_socios' => [
                'required',
                'integer',
            ],
            '*.admissoes' => [
                'required',
                'integer',
            ],
            '*.demissoes' => [
                'required',
                'integer',
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
            'resp_folha.required' => 'A coluna Resp. Folha na planilha é obrigatória.',
            'resp_folha.string' => 'A coluna Resp. Folha na planilha deve ser uma string.',
            'resp_folha.min' => 'A coluna Resp. Folha na planilha deve ter no mínimo 3 caracteres.',
            'anomes.required' => 'A coluna Ano/Mês na planilha é obrigatória.',
            'anomes.string' => 'A coluna Ano/Mês na planilha deve ser uma string.',
            'anomes.min' => 'A coluna Ano/Mês na planilha deve ter no mínimo 6 caracteres.',
            'anomes.max' => 'A coluna Ano/Mês na planilha deve ter no máximo 6 caracteres.',
            'qtde_funcionarios.required' => 'A coluna Qtde Funcionários na planilha é obrigatória.',
            'qtde_funcionarios.integer' => 'A coluna Qtde Funcionários na planilha deve ser um número inteiro.',
            'qtde_socios.required' => 'A coluna Qtde Sócios na planilha é obrigatória.',
            'qtde_socios.integer' => 'A coluna Qtde Sócios na planilha deve ser um número inteiro.',
            'admissoes.required' => 'A coluna Admissões na planilha é obrigatória.',
            'admissoes.integer' => 'A coluna Admissões na planilha deve ser um número inteiro.',
            'demissoes.required' => 'A coluna Demissões na planilha é obrigatória.',
            'demissoes.integer' => 'A coluna Demissões na planilha deve ser um número inteiro.',
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
