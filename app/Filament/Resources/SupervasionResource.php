<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupervasionResource\Pages;
use App\Filament\Resources\SupervasionResource\Pages\UploadSupervasion;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Supervasion;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Support\Enums\Alignment;
use Filament\Actions\StaticAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Collection;

class SupervasionResource extends Resource
{
    protected static ?string $model = Supervasion::class;

    protected static ?string $modelLabel = 'Fiscal';
    protected static ?string $pluralModelLabel = 'Fiscal';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Departamentos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->label('Resp. Fiscal')
                    ->options(Employee::all()->where('departament','Contábil')->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->disabledOn('edit'),
                Forms\Components\Select::make('companies.company_id')
                    ->label('Empresas')
                    ->searchable()
                    ->options(Company::all()->pluck('company_name', 'id'))
                    ->multiple()
                    ->required(),
                Forms\Components\TextInput::make('date')
                    ->label('Data')
                    ->mask('9999/99')
                    ->placeholder('YYYY/MM')
                    ->required()
                    ->maxLength(7)
                    ->minLength(7),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Responsável Fiscal')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\SelectColumn::make('teste')
                    ->label('Empresas')
                    ->options(function (Supervasion $supervasion): array {
                        $currentDate = $supervasion->date;

                        // Empresas associadas até a data mais recente
                        $currentCompanies = $supervasion->companies()
                        ->wherePivot('date', '<=', $currentDate)
                        ->get(['company_name', 'cnpj'])
                        ->pluck('company_name', 'cnpj')
                        ->toArray();

                        // Empresas associadas somente na data específica
                        $historicalCompanies = $supervasion->companies()
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
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->before(function (Supervasion $supervasion, Employee $employee) {
                        self::deleteSupervasionAndManageDepartment($supervasion, $employee);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                    ->before(function (Collection $records, Employee $employee) {
                        self::deleteAllAccountingAndManageDepartment($records, $employee);
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
                                UploadSupervasion::$uploadedFileName = $file->getClientOriginalName();
                                return UploadSupervasion::$uploadedFileName;
                            })
                    ])
                    ->action(function(UploadSupervasion $class){
                        $class->upload();
                    }),
                Tables\Actions\Action::make('import_instructions')
                    ->label("Instruções de Importação")
                    ->color('info')
                    ->modalContent(fn (): View => view(
                        'filament.custom.import_instructions',
                        ['param' => 'Fiscal'],
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
            'index' => Pages\ManageSupervasions::route('/'),
        ];
    }

    public static function deleteAllAccountingAndManageDepartment(Collection $records, Employee $employee)
    {
        $records->each(function ($accounting) use($employee) {

            $employee->find($accounting->employee_id)->companies()->detach();//Remove o vinculo com as empresas

            $result = $employee->find($accounting->employee_id);
            $result->departament = "Definir Departamento"; //Retira o departamento
            $result->save();

            $accounting->delete(); 
        });
    }
    
    public static function deleteSupervasionAndManageDepartment(Supervasion $supervasion, Employee $employee)
    {
        $employeeId = $supervasion->employee_id;
        $date = $supervasion->date;

        // Aqui você remove a entrada na tabela pivot para o funcionário e a data específica
        $employee->find($employeeId)->companies()
            ->wherePivot('date', $date)
            ->detach();
        
        $supervasionCount = $supervasion->where('employee_id', $employeeId)->count();

        if ($supervasionCount === 1) {
            $result = $employee->find($employeeId);
            $result->departament = "Definir Departamento";
            $result->save();
        }
    }
}
