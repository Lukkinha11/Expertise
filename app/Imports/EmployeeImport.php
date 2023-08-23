<?php

namespace App\Imports;

use App\Models\Employee;
use Exception;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EmployeeImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithValidation
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
                        Employee::create([
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

                if (!isset($data['nome']) || !isset($data['email']) || !isset($data['departamento'])) {
                    $validator->errors()->add($key,'É obrigatório a nomeação das colunas na planilha!');

                } else {
                    $user = Employee::where('name', $data['nome'])->where('email', $data['email'])->exists();

                    if ($user) {
                        $validator->errors()->add($key, 'O Funcionário: ' . $data['nome'] . " " . $data['email'] . ' já está cadastrado!');
                    }
                }
            }
        });
    }

    public function rules(): array 
    {
        return [
            '*.nome' => [
                'required',
                'string',
                'min:3'
            ],
            '*.email' => [
                'required',
                'email',
            ],
            '*.departamento' => [
                'required',
            ],
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'nome.string' => 'O campo Nome está inválido e não segue a estrutura exigida.',
            'nome.required' => 'A coluna Nome na planilha é obrigatória.',
            'email.required' => 'A coluna Email na planilha é obrigatória.',
            'email.email' => 'O coluna Email deve ser um email válido.',
            'departamento.required' => 'A coluna Departamento na planilha é obrigatória.',
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
