<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonalDepartamentResource\Pages;
use App\Filament\Resources\PersonalDepartamentResource\Pages\UploadPersonalDepartament;
use Illuminate\Contracts\View\View;
use App\Models\Company;
use App\Models\Employee;
use App\Models\PersonalDepartament;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\Alignment;
use Filament\Actions\StaticAction;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class PersonalDepartamentResource extends Resource
{
    protected static ?string $model = PersonalDepartament::class;

    protected static ?string $modelLabel = 'Resp. Departamento Pessoal';
    protected static ?string $pluralModelLabel = 'Departamento Pessoal';
    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?string $navigationGroup = 'Departamentos';


    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Select::make('employee_id')
                ->label('Resp. Departamento Pessoal')
                ->options(Employee::all()->where('departament','Departamento Pessoal')->pluck('name', 'id'))
                ->searchable()
                ->required()
                ->disabledOn('edit'),
            Forms\Components\TextInput::make('number_of_employees')
                ->label('Qtde. Funcionários')
                ->numeric()
                ->required(),
            Forms\Components\TextInput::make('number_of_partners')
                ->label('Qtde. Sócios')
                ->numeric()
                ->required(),
            Forms\Components\TextInput::make('admissions')
                ->label('Qtde. Admissões')
                ->numeric()
                ->required(),
            Forms\Components\TextInput::make('layoffs')
                ->label('Qtde. Demissões')
                ->numeric()
                ->required(),
            Forms\Components\TextInput::make('date')
                ->label('Data')
                ->mask('9999/99')
                ->placeholder('YYYY/MM')
                ->required()
                ->maxLength(7)
                ->minLength(7),
            Forms\Components\Select::make('companies.company_id')
                ->label('Empresas')
                ->searchable()
                ->options(Company::all()->pluck('company_name', 'id'))
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Responsável DP')
                    ->searchable(),
                Tables\Columns\SelectColumn::make('companies')
                    ->label('Empresas')
                    ->options(function (PersonalDepartament $personalDepartament): array {
                        $currentDate = $personalDepartament->date;
                        // Empresas associadas até a data mais recente
                        $currentCompanies = $personalDepartament->companies()
                            ->wherePivot('date', '<=', $currentDate)
                            ->get(['company_name', 'cnpj'])
                            ->pluck('company_name', 'cnpj')
                            ->toArray();

                        // Empresas associadas somente na data específica
                        $historicalCompanies = $personalDepartament->companies()
                            ->wherePivot('date', $currentDate)
                            ->get(['company_name', 'cnpj'])
                            ->pluck('company_name', 'cnpj')
                            ->toArray();

                        // Combinar as duas listas, mas apenas se houver empresas históricas
                        $combinedCompanies = $historicalCompanies ? array_merge($historicalCompanies, $currentCompanies) : $currentCompanies;

                        return $combinedCompanies;
                    })
                    ->disableOptionWhen(true)
                    ->placeholder('Ver empresas'),
                Tables\Columns\TextColumn::make('date')
                    ->label('Ano/Mês')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->tooltip('Soma da Qtde de funcionários, sócios, admissões e demissões do mês.')
            ])->defaultSort('date', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->before(function (PersonalDepartament $personalDepartament, Employee $employee) {
                        self::deletepersonalDepartamentAndManageDepartment($personalDepartament, $employee);
                    }),
                Tables\Actions\ViewAction::make('view')
                    ->label('Detalhes')
                    ->form([])
                    ->modalContent(fn (PersonalDepartament $personalDepartament): View => view(
                        'filament.custom.view_details_personal_departament',
                        ['personalDepartament' => $personalDepartament],
                    ))
                    ->modalAlignment(Alignment::Center)
                    // ->modalWidth('7xl')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                    ->before(function (Collection $records, Employee $employee) {
                        self::deleteAllpersonalDepartamentAndManageDepartment($records, $employee);
                    }),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('Importar Planilha')
                    ->color('success')
                    ->icon('heroicon-m-arrow-up-tray')
                    ->iconPosition(IconPosition::After)
                    ->form([
                        FileUpload::make('Arquivo Excel(xlsx/xls)')
                            ->required()
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->preserveFilenames()
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) {
                                UploadPersonalDepartament::$uploadedFileName = $file->getClientOriginalName();
                                return UploadPersonalDepartament::$uploadedFileName;
                            })
                    ])
                    ->action(function(UploadPersonalDepartament $class){
                        $class->upload();
                    }),
                Tables\Actions\Action::make('import_instructions')
                    ->label("Instruções de Importação")
                    ->color('info')
                    ->modalContent(fn (): View => view(
                        'filament.custom.import_instructions',
                        ['param' => 'DP'],
                    ))
                    ->modalAlignment(Alignment::Center)
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn (StaticAction $action) => $action->label('Fechar'))
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePersonalDepartaments::route('/'),
        ];
    }

    public static function deleteAllpersonalDepartamentAndManageDepartment(Collection $records, Employee $employee)
    {
        $records->each(function ($personalDepartament) use($employee) {

            $employee->find($personalDepartament->employee_id)->companies()->detach();//Remove o vinculo com as empresas

            $result = $employee->find($personalDepartament->employee_id);
            $result->departament = "Definir Departamento"; //Retira o departamento
            $result->save();

            $personalDepartament->delete(); 
        });
    }
    
    public static function deletepersonalDepartamentAndManageDepartment(PersonalDepartament $personalDepartament, Employee $employee)
    {
        $employeeId = $personalDepartament->employee_id;
        $date = $personalDepartament->date;

        // Aqui você remove a entrada na tabela pivot para o funcionário e a data específica
        $employee->find($employeeId)->companies()
            ->wherePivot('date', $date)
            ->detach();
        
        // Verifica se o funcionário é o único na tabela de contabilidade
        $personalDepartamentCount = $personalDepartament->where('employee_id', $employeeId)->count();

        if ($personalDepartamentCount === 1) {
            $result = $employee->find($employeeId);
            $result->departament = "Definir Departamento";
            $result->save();
        }
    }
}
