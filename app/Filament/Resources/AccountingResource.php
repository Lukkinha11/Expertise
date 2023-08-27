<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountingResource\Pages;
use App\Filament\Resources\AccountingResource\Pages\UploadAccounting;
use App\Models\Accounting;
use App\Models\Company;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;


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
                    ->required(),
                Forms\Components\Select::make('employee.employee_id')
                    ->label('Empresas')
                    ->relationship('companies','company_name')
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
                        return $accounting->companies->pluck('company_name')->toArray();
                    })                
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
                            UploadAccounting::$uploadedFileName = $file->getClientOriginalName();
                            return UploadAccounting::$uploadedFileName;
                        })
                ])
                ->action(function(UploadAccounting $class){
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
            'index' => Pages\ManageAccountings::route('/'),
        ];
    }    
}
