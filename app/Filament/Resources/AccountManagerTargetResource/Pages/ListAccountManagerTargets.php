<?php

namespace App\Filament\Resources\AccountManagerTargetResource\Pages;

use App\Filament\Resources\AccountManagerTargetResource;
use App\Models\AccountManagerTarget;
use App\Models\Order;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class ListAccountManagerTargets extends ListRecords
{
    protected static string $resource = AccountManagerTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('autoGenerate')
                ->label('Auto Generate dari Order')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Auto Generate Target Account Manager')
                ->modalDescription('Fitur ini akan mengambil data dari Order dan membuat target Account Manager berdasarkan achieved_amount dari data Order yang ada. Data yang sudah ada akan diperbarui. Proses ini akan membuat target untuk semua Account Manager dari tahun 2024 sampai bulan berjalan.')
                ->modalSubmitActionLabel('Generate Sekarang')
                ->action(function () {
                    $this->autoGenerateTargets();
                }),

            Actions\CreateAction::make(),
        ];
    }

    public function getTableRecordKey(Model $record): string
    {
        // Ensure we always return a valid string key
        return (string) ($record->getKey() ?? $record->id ?? 'unknown');
    }

    /**
     * Auto generate Account Manager Targets berdasarkan data Order
     */
    public function autoGenerateTargets(): void
    {
        try {
            DB::beginTransaction();

            // Ambil semua Account Manager (user dengan role Account Manager)
            $accountManagers = User::whereHas('roles', function ($query) {
                $query->where('name', 'Account Manager');
            })->get();

            // Validasi: pastikan ada Account Manager
            if ($accountManagers->isEmpty()) {
                Notification::make()
                    ->title('Tidak Ada Account Manager!')
                    ->body('Tidak ditemukan user dengan role Account Manager. Pastikan role sudah dibuat dan user sudah di-assign.')
                    ->warning()
                    ->send();
                return;
            }

            // Validasi: pastikan ada data Order yang valid
            $totalValidOrders = Order::whereNotNull('closing_date')
                ->where('total_price', '>', 0)
                ->whereIn('user_id', $accountManagers->pluck('id'))
                ->count();

            if ($totalValidOrders == 0) {
                Notification::make()
                    ->title('Tidak Ada Data Order Valid!')
                    ->body('Tidak ditemukan Order dengan closing_date dan total_price yang valid untuk Account Manager yang ada. Pastikan data Order sudah lengkap.')
                    ->warning()
                    ->send();
                return;
            }

            $generatedCount = 0;
            $updatedCount = 0;
            $totalTargets = 0;

            // Hitung total target yang akan diproses untuk progress
            $currentYear = Carbon::now()->year;
            $startYear = 2024;
            $totalMonths = 0;
            for ($year = $startYear; $year <= $currentYear; $year++) {
                $maxMonth = ($year == $currentYear) ? Carbon::now()->month : 12;
                $totalMonths += $maxMonth;
            }
            $totalTargets = $accountManagers->count() * $totalMonths;

            foreach ($accountManagers as $am) {
                // Generate target untuk semua bulan dari 2024 sampai sekarang
                for ($year = $startYear; $year <= $currentYear; $year++) {
                    $maxMonth = ($year == $currentYear) ? Carbon::now()->month : 12;

                    for ($month = 1; $month <= $maxMonth; $month++) {
                        // Hitung achieved amount dari Orders menggunakan total_price
                        // karena grand_total di database sering bernilai NULL
                        $achievedAmount = Order::where('user_id', $am->id)
                            ->whereNotNull('closing_date')
                            ->whereYear('closing_date', $year)
                            ->whereMonth('closing_date', $month)
                            ->sum('total_price') ?? 0;

                        // Hitung status berdasarkan pencapaian
                        $targetAmount = 1000000000.00; // Default target 1 milyar

                        $status = 'pending';
                        if ($achievedAmount >= $targetAmount) {
                            $status = 'achieved';
                        } elseif ($achievedAmount >= ($targetAmount * 0.8)) {
                            $status = 'on_track';
                        } elseif ($achievedAmount > 0) {
                            $status = 'behind';
                        }

                        // Check apakah record sudah ada (termasuk soft deleted)
                        $existingTarget = AccountManagerTarget::withTrashed()->where([
                            'user_id' => $am->id,
                            'year' => $year,
                            'month' => $month
                        ])->first();

                        if ($existingTarget) {
                            // Jika target soft deleted, restore dulu
                            if ($existingTarget->trashed()) {
                                $existingTarget->restore();
                            }

                            // Update existing record
                            $existingTarget->update([
                                'target_amount' => $targetAmount,
                                'achieved_amount' => $achievedAmount,
                                'status' => $status
                            ]);
                            $updatedCount++;
                        } else {
                            // Create new record dengan error handling
                            try {
                                AccountManagerTarget::create([
                                    'user_id' => $am->id,
                                    'year' => $year,
                                    'month' => $month,
                                    'target_amount' => $targetAmount,
                                    'achieved_amount' => $achievedAmount,
                                    'status' => $status
                                ]);
                                $generatedCount++;
                            } catch (\Illuminate\Database\QueryException $e) {
                                // Jika tetap error duplicate, coba update (termasuk soft deleted)
                                if ($e->getCode() == 23000) {
                                    $existingTarget = AccountManagerTarget::withTrashed()->where([
                                        'user_id' => $am->id,
                                        'year' => $year,
                                        'month' => $month
                                    ])->first();

                                    if ($existingTarget) {
                                        $existingTarget->update([
                                            'target_amount' => $targetAmount,
                                            'achieved_amount' => $achievedAmount,
                                            'status' => $status
                                        ]);
                                        $updatedCount++;
                                    }
                                } else {
                                    throw $e; // Re-throw jika bukan duplicate error
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();

            // Hitung statistik akhir
            $totalAchieved = AccountManagerTarget::where('achieved_amount', '>', 0)->count();
            $totalRevenue = AccountManagerTarget::sum('achieved_amount');

            Notification::make()
                ->title('Auto Generate Berhasil! 🎉')
                ->body("✅ {$generatedCount} target baru dibuat\n✅ {$updatedCount} target diperbarui\n📊 {$totalAchieved} target memiliki pencapaian\n💰 Total pencapaian: Rp " . number_format($totalRevenue))
                ->success()
                ->send();

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Auto Generate Gagal! ❌')
                ->body('Terjadi kesalahan: ' . $e->getMessage() . '. Silakan coba lagi atau hubungi administrator.')
                ->danger()
                ->send();
        }
    }

    protected function getHeaderWidgets(): array
    {
        return AccountManagerTargetResource::getWidgets();
    }

}
