<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Regency;
use App\Models\Village;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\District;
use App\Models\Supplier;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SupplierResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SupplierResource\RelationManagers;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationGroup = 'Parties';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Section::make('Company Information')
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(20),
            ])
            ->columns(3),
            Section::make('Company Address')
            ->schema([
                Forms\Components\TextInput::make('address')
                    ->maxLength(200)
                    ->columnSpanFull(),
                    Forms\Components\Select::make('province_id')
                        ->relationship('province', 'name')
                        ->live()
                        ->searchable()
                        ->preload()
                        ->afterStateUpdated(function (Set $set) {
                            $set('regency_id', null);
                            $set('district_id', null);
                            $set('village_id', null);
                        })
                        ->required(),
                    Forms\Components\Select::make('regency_id')
                        ->options(fn (Get $get) => Regency::query()->where('province_id', $get('province_id'))->pluck('name', 'id'))
                        ->required()
                        ->live()
                        ->searchable()
                        ->afterStateUpdated(function (Set $set) {
                            $set('district_id', null);
                            $set('village_id', null);
                        })
                        ->preload(),
                    Forms\Components\Select::make('district_id')
                        ->afterStateUpdated(function (Set $set) {
                            $set('village_id', null);
                        })
                        ->options(fn (Get $get) => District::query()->where('regency_id', $get('regency_id'))->pluck('name', 'id'))
                        ->searchable()
                        ->live()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('village_id')
                        ->options(fn (Get $get) => Village::query()->where('district_id', $get('district_id'))->pluck('name', 'id'))
                        ->searchable()
                        ->live()
                        ->preload()
                        ->required(),
            ])
            ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('province.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('regency.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('district.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('village.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'view' => Pages\ViewSupplier::route('/{record}'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
