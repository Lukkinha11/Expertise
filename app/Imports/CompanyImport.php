<?php

namespace App\Imports;

use App\Models\Company;
use App\Models\Employee;
use Exception;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;

class CompanyImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithValidation
{
    use Importable;

    public $exceptionTransation; // variavel responsavel por retornar exceptions ao efetuar cadastros no banco
    public $employeesAndCompany; // variavel responsavel por retornar os responsaveis e o id da empresa cadastrada no banco

    /**
    * @param Collection $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function collection(Collection $rows)
    {
        try {
            DB::transaction(function () use ($rows) {
                foreach ($rows as $row) {
                    if ($this->isValidRow($row)) {

                        $company = Company::create([
                            'company_name' => $row['nome_empresa'],
                            'cnpj' => $row['cnpj'],
                            'code' => $row['codigo'],
                            'branch' => $row['filial'],
                        ]);

                        $this->employeesAndCompany[] = [
                            'responsible' => $row['responsavel'],
                            'company_id' => $company->id, // Armazene o ID da empresa junto com o nome do responsável
                        ];
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

                if (!isset($data['nome_empresa']) || !isset($data['codigo']) || !isset($data['filial']) || !isset($data['cnpj']) || !isset($data['responsavel'])) {
                    $validator->errors()->add($key,'É obrigatório a nomeação das colunas na planilha!');

                } else {
                    $user = Company::where('cnpj', $data['cnpj'])->exists();

                    if ($user) {
                        $validator->errors()->add($key, 'A Empresa: ' . $data['nome_empresa'] . " " . $data['cnpj'] . ' já está cadastrada!');
                    }
                }
            }
        });
    }

    public function rules(): array 
    {
        return [
            '*.nome_empresa' => [
                'required',
                'string',
                'min:3'
            ],
            '*.codigo' => [
                'required',
                'integer',
            ],
            '*.filial' => [
                'required',
                'integer',
            ],
            '*.cnpj' => [
                'required',
                'cnpj',
            ],
            '*.responsavel' => [
                'required',
                'string',
                'min:3'
            ],
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
{
    return [
        'nome_empresa.required' => 'A coluna Nome Empresa na planilha é obrigatória.',
        'nome_empresa.string' => 'A coluna Nome Empresa na planilha deve ser uma texto.',
        'nome_empresa.min' => 'A coluna Nome Empresa na planilha deve ter no mínimo 3 caracteres.',
        'codigo.required' => 'A coluna Código na planilha é obrigatória.',
        'codigo.integer' => 'A coluna Código na planilha deve ser um número inteiro.',
        'filial.required' => 'A coluna Filial na planilha é obrigatória.',
        'filial.integer' => 'A coluna Filial na planilha deve ser um número inteiro.',
        'cnpj.required' => 'A coluna CNPJ na planilha é obrigatória.',
        'cnpj.cnpj' => 'A coluna CNPJ na planilha deve ser um CNPJ válido.',
        'responsavel.required' => 'A coluna Responsável na planilha é obrigatória.',
        'responsavel.string' => 'A coluna Responsável na planilha deve ser uma string.',
        'responsavel.min' => 'A coluna Responsável na planilha deve ter no mínimo 3 caracteres.',
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
