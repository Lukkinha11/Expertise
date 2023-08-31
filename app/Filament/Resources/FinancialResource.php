<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FinancialResource\Pages;
use App\Models\Company;
use App\Models\Financial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Leandrocfe\FilamentPtbrFormFields\Money;

class FinancialResource extends Resource
{
    protected static ?string $model = Financial::class;

    protected static ?string $modelLabel = 'Financeiro';
    protected static ?string $pluralModelLabel = 'Financeiro';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Departamentos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()->schema([
                    Forms\Components\Select::make('company_id')
                        ->label('Empresa')
                        ->searchable()
                        ->relationship('company', 'company_name')
                        ->options(Company::all()->map(function ($company) {
                            return [
                                'id' => $company->id,
                                'label' => $company->company_name . ' - ' . $company->cnpj,
                            ];
                        })->pluck('label', 'id'))
                        ->getSearchResultsUsing(function (string $search): array {
                            return Company::where('company_name', 'like', "%{$search}%")
                                ->limit(50)
                                ->get()
                                ->map(function ($company) {
                                    return [
                                        'id' => $company->id,
                                        'label' => $company->company_name . ' - ' . $company->cnpj,
                                    ];
                                })
                                ->pluck('label', 'id')
                                ->toArray();
                        })
                        ->getOptionLabelUsing(function ($value): ?string {
                            $company = Company::find($value);
                            return $company ? $company->company_name . ' - ' . $company->cnpj : null;
                        })
                        ->required(),
                    Forms\Components\DatePicker::make('accrual_date')
                        ->label('Data de competência')
                        ->required(),
                ])->columns(2),
                Forms\Components\Grid::make()->schema([
                    Forms\Components\DatePicker::make('due_date')
                        ->label('Data de vencimento')
                        ->required(),
                    Forms\Components\DatePicker::make('expected_date')
                        ->label('Data prevista')
                        ->required(),
                    Forms\Components\Select::make('description')
                        ->label('Descrição')
                        ->options([
                            'venda' => 'Venda',
                        ])
                        ->native(false)
                        ->required(),
                ])->columns(3),
                Forms\Components\Grid::make()->schema([
                    Money::make('original_value')
                        ->label('Valor original da parcela')
                        ->prefix('R$')
                        ->required(),
                    Forms\Components\TextInput::make('receipt_form')
                        ->label('Forma de recebimento')
                        ->default('Boleto bancário')
                        ->required()
                        ->maxLength(50),
                    Money::make('installment_value')
                        ->label('Valor recebido da parcela')
                        ->prefix('R$')
                        ->required(),
                ])->columns(3),
                Forms\Components\Grid::make()->schema([
                    Money::make('interest_realized')
                        ->label('Juros realizado')
                        ->prefix('R$')
                        ->required(),
                    Money::make('fine_performed')
                        ->label('Multa realizada')
                        ->prefix('R$')
                        ->required(),
                    Money::make('discont_made')
                        ->label('Desconto realizado')
                        ->prefix('R$')
                        ->required(),
                ])->columns(3),
                Forms\Components\Grid::make()->schema([
                    Money::make('total_amount_received_installment')
                        ->label('Valor total recebido da parcela')
                        ->prefix('R$')
                        ->required(),
                    Money::make('open_installment_value')
                        ->label('Valor da parcela em aberto')
                        ->prefix('R$')
                        ->required(),
                    Money::make('expected_interest')
                        ->label('Juros previsto')
                        ->prefix('R$')
                        ->required(),
                ])->columns(3),
                Forms\Components\Grid::make()->schema([
                    Money::make('expected_fine')
                        ->label('Multa prevista')
                        ->prefix('R$')
                        ->required(),
                    Money::make('expected_discount')
                        ->label('Desconto previsto')
                        ->prefix('R$')
                        ->required(),
                    Money::make('total_amount_open_installment')
                        ->label('Valor total da parcela em aberto')
                        ->prefix('R$')
                        ->required(),
                ])->columns(3),
                Forms\Components\Grid::make()->schema([
                    Forms\Components\TextInput::make('bank_account')
                        ->label('Conta bancária')
                        ->default('Banco Sicoob Gomide')
                        ->required()
                        ->maxLength(100),
                    Forms\Components\TextInput::make('category_1')
                        ->label('Categoria 1')
                        ->default('Recebimentos de Mensalidades')
                        ->required()
                        ->maxLength(100),
                    Money::make('value_category_1')
                        ->label('Valor na Categoria 1')
                        ->prefix('R$')
                        ->required(),
                ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.company_name')
                    ->label('Empresa')
                    ->sortable()
                    ->searchable()
                    ->tooltip(fn (Financial $financial) => $financial->company->cnpj),
                Tables\Columns\TextColumn::make('accrual_date')
                    ->label('Data de competência')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Data de vencimento')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('expected_date')
                    ->label('Data prevista')
                    ->dateTime('d/m/Y')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable(),
                Tables\Columns\TextColumn::make('original_value')
                    ->label('Valor original da parcela')
                    ->money('brl')
                    ->searchable(),
                Tables\Columns\TextColumn::make('receipt_form')
                    ->label('Forma de recebimento')
                    ->searchable(),
                Tables\Columns\TextColumn::make('installment_value')
                    ->label('Valor recebido da parcela')
                    ->money('brl')
                    ->searchable(),
                Tables\Columns\TextColumn::make('interest_realized')
                    ->label('Juros realizado')
                    ->money('brl')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fine_performed')
                    ->label('Multa realizada')
                    ->money('brl')
                    ->searchable(),
                Tables\Columns\TextColumn::make('discont_made')
                    ->label('Desconto realizado')
                    ->money('brl')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_amount_received_installment')
                    ->label('Valor total recebido da parcela')
                    ->money('brl')
                    ->searchable(),
                Tables\Columns\TextColumn::make('open_installment_value')
                    ->label('Valor da parcela em aberto')
                    ->money('brl')
                    ->searchable(),
                Tables\Columns\TextColumn::make('expected_interest')
                    ->label('Juros previsto')
                    ->money('brl')
                    ->searchable(),
                Tables\Columns\TextColumn::make('expected_fine')
                    ->label('Multa prevista')
                    ->money('brl')
                    ->searchable(),
                Tables\Columns\TextColumn::make('expected_discount')
                    ->label('Desconto previsto')
                    ->money('brl')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_amount_open_installment')
                    ->label('Valor total da parcela em aberto')
                    ->money('brl')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bank_account')
                    ->label('Conta bancária')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category_1')
                    ->label('Recebimentos de Mensalidades')
                    ->searchable(),
                Tables\Columns\TextColumn::make('value_category_1')
                    ->label('Valor na Categoria 1')
                    ->money('brl')
                    ->searchable(),
            ])->defaultSort('accrual_date', 'desc')
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
            'index' => Pages\ListFinancials::route('/'),
            'create' => Pages\CreateFinancial::route('/create'),
            'edit' => Pages\EditFinancial::route('/{record}/edit'),
        ];
    }    
}
