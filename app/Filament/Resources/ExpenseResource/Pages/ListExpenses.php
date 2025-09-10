<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ExpenseResource\Widgets\ExpenseOverview;
use App\Imports\ExpensesImport;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\FileUpload;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            // --- Tombol Download Template ---
            // Action::make('downloadTemplate')
            //     ->label('Download Template')
            //     ->icon('heroicon-o-document-arrow-down')
            //     ->url(asset('templates/template_import_expense.xlsx'), shouldOpenInNewTab: false) 
            //     ->color('gray'),

            // Action::make('importExpenses')
            //     ->label('Import Expenses')
            //     ->color('danger')
            //     ->icon('heroicon-o-arrow-down-tray')
            //     ->form([
            //         FileUpload::make('attachment')
            //             ->label('Excel File')
            //             ->disk('local') 
            //             ->directory('expense-imports') 
            //             ->acceptedFileTypes([
            //                 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            //                 'application/vnd.ms-excel'
            //             ])
            //             ->maxSize(10240)
            //             ->required()
            //             ->helperText('Upload Excel file (.xlsx, .xls) with expense data'),
            //     ])
            //     ->action(function (array $data): void {
            //         try {

                //         if (!isset($data['attachment']) || !Storage::disk('local')->exists($data['attachment'])) {
                //             throw new \Exception('Upload file not found. Please try again.');
                //         }

                //         $filePath = Storage::disk('local')->path($data['attachment']);

                //         Excel::import(new ExpensesImport(), $filePath);

                //         Notification::make()
                //             ->success()
                //             ->title('Expenses imported successfully.')
                //             ->send();
                //     } catch (\Exception $e) {
                //         Notification::make()
                //             ->danger()
                //             ->title('Failed to import expenses.')
                //             ->body($e->getMessage())
                //             ->send();
                //     }
                // }),

            // Action::make('viewHtmlReport')
            //     ->label('Laporan Pengeluaran')
            //     ->icon('heroicon-o-document-text')
            //     ->url(route('expense.html-report'), true) 
            //     ->color('info'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ExpenseOverview::class,
        ];
    }
}
