<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RentalResource\Pages;
use App\Filament\Resources\RentalResource\RelationManagers;
use App\Models\Rental;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RentalResource extends Resource
{
    protected static ?string $model = Rental::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Sewa Konsol';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('console_id')
                    ->relationship(
                        name: 'console',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn($query) => $query->where('status', 'available')
                    )
                    ->required()
                    ->label('Konsol (Tersedia Saja)'),
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required()
                    ->label('Pelanggan'),
                Forms\Components\DateTimePicker::make('start_time')
                    ->default(now())
                    ->required()
                    ->timezone('Asia/Jakarta')
                    ->label('Mulai Sewa'),
                Forms\Components\TextInput::make('duration_hours')
                    ->numeric()
                    ->required()
                    ->label('Durasi (jam)'),
                Forms\Components\Toggle::make('is_paid')->label('Lunas?'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('console.name')->label('Konsol'),
                Tables\Columns\TextColumn::make('customer.name')->label('Pelanggan'),
                Tables\Columns\TextColumn::make('start_time')->dateTime()->timezone('Asia/Jakarta'),
                Tables\Columns\TextColumn::make('end_time')->dateTime()->timezone('Asia/Jakarta'),
                Tables\Columns\TextColumn::make('total_cost')->money('IDR'),
                Tables\Columns\IconColumn::make('is_paid')->boolean()->label('Lunas'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('endRental')
                    ->label('Finish Now')
                    ->icon('heroicon-o-stop')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->console->status === 'in_use')
                    ->color('danger')
                    ->action(function ($record) {
                        $now = Carbon::now();
                        $start = Carbon::parse($record->start_time);
                        $duration = ceil($start->diffInMinutes($now) / 60);

                        $console = $record->console;

                        // Update rental info
                        $record->update([
                            'end_time' => $now,
                            'duration_hours' => $duration,
                            'total_cost' => $console->rate_per_hour * $duration,
                            'is_paid' => true, // optional, tergantung model kamu
                        ]);

                        // Update console status
                        $console->update(['status' => 'available']);

                        // Matikan TV lewat Firebase
                        app(FirebaseService::class)->setConsolePower($console->id, 0);
                    }),
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
            'index' => Pages\ListRentals::route('/'),
            'create' => Pages\CreateRental::route('/create'),
            'edit' => Pages\EditRental::route('/{record}/edit'),
        ];
    }
}
