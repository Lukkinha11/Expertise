<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupervasionResource\Pages;
use App\Filament\Resources\SupervasionResource\Pages\UploadSupervasion;
use App\Filament\Resources\SupervasionResource\RelationManagers;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Supervasion;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SupervasionResource extends Resource
{
    protected static ?string $model = Supervasion::class;

    protected static ?string $modelLabel = 'Fiscal';
    protected static ?string $pluralModelLabel = 'Fiscal';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employee_id')
                    ->label('Resp. Fiscal')
                    ->options(Employee::all()->pluck('name', 'id'))
                    ->searchable()
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
        // $teste = Supervasion::select('employee_id')->with('employee.companies:company_name')->where('employee_id', 647)->first();        
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Responsável Fiscal')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\SelectColumn::make('employee.companies')
                    ->label('Empresas')
                    ->options(function (Supervasion $supervasion): array {

                        $companies = Supervasion::select('date','companies.company_name', 'supervasion.employee_id')
                            ->join('employees', 'supervasion.employee_id', '=', 'employees.id')
                            ->join('employees_companies', 'employees.id', '=', 'employees_companies.employee_id')
                            ->join('companies', 'companies.id', '=', 'employees_companies.company_id')
                            ->where('date', $supervasion->date)
                            ->where('supervasion.employee_id', $supervasion->employee_id)
                            ->orderBy('companies.company_name', 'asc')
                            ->get()
                            ->pluck('company_name')
                            ->toArray();
                    
                        return $companies;
                    })                
                    // ->options(function (Supervasion $supervasion): array {
                    //     return $supervasion->employee->companies()->pluck('company_name')->toArray();
                    // })                
                    ->selectablePlaceholder(false),
                Tables\Columns\TextColumn::make('date')
                    ->label('Ano/Mês')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('Importar Planilha')
                ->color('info')
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
                })
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
}
