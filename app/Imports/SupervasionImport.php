<?php

namespace App\Imports;

use App\Models\Supervasion;
use Exception;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SupervasionImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithValidation
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
        dd($rows);
        try {
            DB::transaction(function () use ($rows) {
                foreach ($rows as $row) {
                    if ($this->isValidRow($row)) {
                        Supervasion::create([
                            'name' => $row['nome'],
                            'email' => $row['email'],
                            'departament' => $row['departamento'],
                        ]);
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

                // if (!isset($data['nome']) || !isset($data['email']) || !isset($data['departamento'])) {
                //     $validator->errors()->add($key,'É obrigatório a nomeação das colunas na planilha!');

                // } else {
                //     $user = Supervasion::where('name', $data['nome'])->where('email', $data['email'])->exists();

                //     if ($user) {
                //         $validator->errors()->add($key, 'O Funcionário: ' . $data['nome'] . " " . $data['email'] . ' já está cadastrado!');
                //     }
                // }
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
                'cnpj'
            ],
            '*.resp_fiscal' => [
                'required',
                'string',
                'min:3'
            ],
            '*anomes' => [
                'required',
                'string',
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
            'cnpj.required' => 'A coluna CNPJ na planilha é obrigatória.',
            'cnpj.cnpj' => 'A coluna CNPJ na planilha deve ser um CNPJ válido.',
            'resp_fiscal.required' => 'A coluna Resp. Fiscal na planilha é obrigatória.',
            'resp_fiscal.string' => 'A coluna Resp. Fiscal na planilha deve ser uma string.',
            'resp_fiscal.min' => 'A coluna Resp. Fiscal na planilha deve ter no mínimo 3 caracteres.',
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
