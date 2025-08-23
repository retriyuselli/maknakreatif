<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\SimulasiProdukResource; // Pastikan namespace ini benar jika digunakan
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Product') // Anda bisa menyesuaikan label jika perlu
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Pembuatan Produk Baru')
                ->modalDescription('Pastikan kembali data yang akan Anda isi sudah benar sebelum melanjutkan.')
                ->modalSubmitActionLabel('Lanjutkan')
                ->modalCancelActionLabel('Batal'),
            
            // Aksi 'penawaran' Anda yang sudah ada
            Actions\Action::make('penawaran')
                ->label('Penawaran')
                ->color('success')
                ->icon('heroicon-o-eye')
                ->url(SimulasiProdukResource::getUrl('create')) // Pastikan SimulasiProdukResource di-import atau gunakan FQCN
                ->openUrlInNewTab(),
        ];
    }
}
 