<?php

namespace App\Filament\Resources\RentalResource\Pages;

use App\Filament\Resources\RentalResource;
use App\Models\Console;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateRental extends CreateRecord
{
    protected static string $resource = RentalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ambil tarif dari konsol
        $console = Console::find($data['console_id']);

        if (!$console || $console->status !== 'available') {
            throw ValidationException::withMessages([
                'console_id' => 'Konsol yang dipilih sedang tidak tersedia.',
            ]);
        }

        $start = Carbon::parse($data['start_time']);
        // Cast durasi ke integer agar tidak error di Carbon
        $duration = (int) $data['duration_hours'];

        // Hitung end_time dari waktu UTC
        $end = $start->copy()->addHours($duration);

        $data['start_time'] = $start;
        $data['end_time'] = $end;
        $data['duration_hours'] = $duration;
        $data['total_cost'] = $console->rate_per_hour * $duration;

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        $console = $record->console;

        // Ubah status konsol menjadi in_use
        $console->update(['status' => 'in_use']);

        // Trigger Firebase ON (hidupkan TV)
        app(FirebaseService::class)->setConsolePower($console->id, 1);

        // Jadwalkan OFF otomatis setelah durasi sewa selesai
        dispatch(function () use ($console) {
            app(FirebaseService::class)->setConsolePower($console->id, 0);
            $console->update(['status' => 'available']);
        })->delay(now()->addHours((int)$record->duration_hours));
    }
}
