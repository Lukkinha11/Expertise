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
use Filament\Notifications\Notification;
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
                    ->required()
                    ->disabledOn('edit'),
                Forms\Components\Select::make('employee.employee_id')
                    ->label('Empresas')
                    ->relationship('companies','company_name')
                    ->searchable()
                    ->options(Company::all()->pluck('company_name', 'id'))
                    ->multiple(),
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
                Tables\Columns\SelectColumn::make('companies')
                    ->label('Empresas')
                    ->options(function (Supervasion $supervasion): array {
                        return $supervasion->companies->pluck('company_name')->toArray();
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
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data, Tables\Actions\EditAction $action): array {
                        $slashPosition = strpos($data['date'], '/');
                        $dateParts = explode('/', $data['date']);

                        if ($slashPosition !== 4) {
                                                    
                            $formattedDate = $dateParts[1] . '/' . $dateParts[0];
                            $data['date'] = $formattedDate;
                        }
                        $month = $dateParts[1];
                        $year = $dateParts[0];

                        if ($month < 1 || $month > 12 || $year < 1900 || $year > date('Y')) {
                            
                            Notification::make()
                                ->danger()
                                ->title('Formato incorreto de Data!')
                                ->body('A data deve está no formato Ano/mês.')
                                ->persistent()
                                ->send();                
                            $action->halt();
                        }
                        return $data;
                    }),
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
