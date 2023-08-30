<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\Pages\UploadEmployee;
use App\Models\Company;
use App\Models\Employee;
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

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $modelLabel = 'Funcionário';
    protected static ?string $pluralModelLabel = 'Funcionários';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->unique(ignoreRecord:true)
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('departament')
                    ->label('Departamento')
                    ->options(Employee::getDepartmentOptions())
                    ->required(),
                Forms\Components\Select::make('employees_companies')
                    ->label('Empresas')
                    ->relationship('companies', 'company_name')
                    ->options(Company::all()->pluck('company_name', 'id'))
                    ->multiple()
            ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('departament')
                    ->label('Departamento')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\SelectColumn::make('companies')
                    ->label('Empresas')
                    ->options(function (Employee $employee): array {
                        return $employee->companies()->pluck('company_name')->toArray();
                    })
                    ->disableOptionWhen(true)            
                    ->placeholder('Ver empresas do Funcionário'),
                Tables\Columns\TextColumn::make('companies_count')
                    ->label('Qtd Empresas')
                    ->counts('companies')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
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
                            UploadEmployee::$uploadedFileName = $file->getClientOriginalName();
                            return UploadEmployee::$uploadedFileName;
                        })
                ])
                ->action(function(UploadEmployee $class){
                    $class->upload();
                })
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }    
}
