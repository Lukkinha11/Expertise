<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountingResource\Pages;
use App\Filament\Resources\AccountingResource\Pages\UploadAccounting;
use App\Models\Accounting;
use App\Models\Company;
use App\Models\Employee;
use Filament\Actions\StaticAction;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Illuminate\Contracts\View\View;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Database\Eloquent\Collection;

class AccountingResource extends Resource
{
    protected static ?string $model = Accounting::class;

    protected static ?string $modelLabel = 'Contábil';
    protected static ?string $pluralModelLabel = 'Contábil';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->label('Resp. Contábil')
                    ->options(Employee::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->disabledOn('edit'),
                Forms\Components\Select::make('companies.company_id')
                    ->label('Empresas')
                    ->searchable()
                    ->options(Company::all()->pluck('company_name', 'id'))
                    ->multiple(),
                Forms\Components\TextInput::make('date')
                    ->label('Data(Ano/Mês)' )
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
                    ->label('Responsável Contábil')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\SelectColumn::make('companies')
                    ->label('Empresas')
                    ->options(function (Accounting $accounting): array {
                        $currentDate = $accounting->date;

                        // Empresas associadas até a data mais recente
                        $currentCompanies = $accounting->companies()
                            ->wherePivot('date', '<=', $currentDate)
                            ->get(['company_name', 'cnpj'])
                            ->pluck('company_name', 'cnpj')
                            ->toArray();

                        // Empresas associadas somente na data específica
                        $historicalCompanies = $accounting->companies()
                            ->wherePivot('date', $currentDate)
                            ->get(['company_name', 'cnpj'])
                            ->pluck('company_name', 'cnpj')
                            ->toArray();

                        // Combinar as duas listas, mas apenas se houver empresas históricas
                        $combinedCompanies = $historicalCompanies ? array_merge($historicalCompanies, $currentCompanies) : $currentCompanies;

                        return $combinedCompanies;
                    })
                    ->disableOptionWhen(true)
                    ->placeholder('Ver empresas do Resp. Contábil'),
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
                    ->before(function (Accounting $accounting, Employee $employee) {
                        self::deleteAccountingAndManageDepartment($accounting, $employee);
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
                                UploadAccounting::$uploadedFileName = $file->getClientOriginalName();
                                return UploadAccounting::$uploadedFileName;
                            })
                    ])
                    ->action(function(UploadAccounting $class){
                        $class->upload();
                    }),
                Tables\Actions\Action::make('import_instructions')
                    ->label("Instruções de Importação")
                    ->color('info')
                    ->modalContent(fn (): View => view(
                        'filament.custom.import_instructions',
                        ['param' => 'Contábil'],
                    ))
                    ->modalAlignment(Alignment::Center)
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn (StaticAction $action) => $action->label('Fechar'))
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAccountings::route('/'),
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
    
    public static function deleteAccountingAndManageDepartment(Accounting $accounting, Employee $employee)
    {
        $employeeId = $accounting->employee_id;
        $date = $accounting->date;

        // Aqui você remove a entrada na tabela pivot para o funcionário e a data específica
        $employee->find($employeeId)->companies()
            ->wherePivot('date', $date)
            ->detach();
        
        // Verifica se o funcionário é o único na tabela de contabilidade
        $accountingCount = $accounting->where('employee_id', $employeeId)->count();

        if ($accountingCount === 1) {
            $result = $employee->find($employeeId);
            $result->departament = "Definir Departamento";
            $result->save();
        }
    }
}
