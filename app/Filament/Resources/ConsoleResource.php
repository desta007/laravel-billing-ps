<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConsoleResource\Pages;
use App\Filament\Resources\ConsoleResource\RelationManagers;
use App\Models\Console;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConsoleResource extends Resource
{
    protected static ?string $model = Console::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    static ?string $navigationLabel = 'Konsol PS';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('rate_per_hour')
                ->required()
                ->numeric()
                ->label('Tarif per Jam'),
            Forms\Components\Select::make('status')
                ->options(['available' => 'Tersedia', 'in_use' => 'Disewa'])
                ->default('available')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('rate_per_hour')->label('Tarif/Jam')->money('IDR'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'available' => 'success',
                        'in_use' => 'warning',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'available' => 'Tersedia',
                        'in_use' => 'Disewa',
                        default => ucfirst($state),
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListConsoles::route('/'),
            'create' => Pages\CreateConsole::route('/create'),
            'edit' => Pages\EditConsole::route('/{record}/edit'),
        ];
    }
}
