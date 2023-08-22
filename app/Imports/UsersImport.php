<?php

namespace App\Imports;

use App\Models\User;
use Exception;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


class UsersImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithValidation
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
            //Fazer validações se já existe o registro ou n
            DB::transaction(function () use ($rows) {
                foreach ($rows as $row) {
                    if ($this->isValidRow($row)) {
                        User::create([
                            'name' => $row['nome'],
                            'email' => $row['email'],
                            'password' => Hash::make($row['senha']),
                        ]);
                    }
                }
            });

        } catch (Exception $e) {

            return $this->exceptionTransation = $e;
        }
    }

    /**
     * Validação customizada para verificar se o produto já existe no banco de dados
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            
            foreach ($validator->getData() as $key => $data) {

                if (!isset($data['nome']) || !isset($data['email']) || !isset($data['senha'])) {
                    $validator->errors()->add($key,'É obrigatório a nomeação das colunas na planilha!');

                } else {
                    $user = User::where('name', $data['nome'])->where('email', $data['email'])->exists();

                    if ($user) {
                        $validator->errors()->add($key, 'O Usuário: ' . $data['nome'] . " " . $data['email'] . ' já está cadastrado!');
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
            '*.senha' => [
                'required',
                'min:8'
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
            'senha.required' => 'A coluna Senha na planilha é obrigatória.',
            'senha.min' => 'O tamanho minímo da senha deve ser de 8 caracteres.',
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
