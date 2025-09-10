<?php

namespace App\Filament\Resources\VendorResource\Pages;

use App\Filament\Resources\VendorResource;
use App\Models\Vendor;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class EditVendor extends EditRecord
{
    protected static string $resource = VendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->icon('heroicon-m-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Delete Vendor')
                ->modalDescription('Are you sure you want to delete this vendor? This action cannot be undone.')
                ->modalSubmitActionLabel('Yes, delete')
                ->modalIcon('heroicon-o-exclamation-triangle')
                ->modalIconColor('danger')
                ->visible(function (): bool {
                    $record = $this->getRecord();
                    if (!$record) return false;
                    
                    $productCount = $record->productVendors()->count();
                    $expenseCount = $record->vendors()->count();
                    $notaDinasCount = $record->notaDinasDetails()->count();
                    return $productCount === 0 && $expenseCount === 0 && $notaDinasCount === 0;
                })
                ->before(function () {
                    $record = $this->getRecord();
                    if (!$record) {
                        Notification::make()
                            ->danger()
                            ->title('Error')
                            ->body('Vendor data not found. Please refresh the page and try again.')
                            ->persistent()
                            ->send();
                        return false;
                    }
                    
                    Notification::make()
                        ->info()
                        ->title('Processing')
                        ->body('Validating vendor for deletion...')
                        ->send();
                })
                ->action(function () {
                    $record = $this->getRecord();
                    if (!$record) {
                        Notification::make()
                            ->danger()
                            ->title('Deletion Failed')
                            ->body('Vendor data not found. May have been already deleted or moved.')
                            ->persistent()
                            ->send();
                        return false;
                    }

                    try {
                        $record->refresh();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Deletion Failed')
                            ->body('Cannot access vendor data. May have been deleted by another user.')
                            ->persistent()
                            ->send();
                        return false;
                    }

                    // Double check for associations
                    $productCount = $record->productVendors()->count();
                    $expenseCount = $record->vendors()->count();
                    $notaDinasCount = $record->notaDinasDetails()->count();
                    
                    if ($productCount > 0 || $expenseCount > 0 || $notaDinasCount > 0) {
                        $details = [];
                        if ($productCount > 0) {
                            $details[] = "{$productCount} product(s)";
                        }
                        if ($expenseCount > 0) {
                            $details[] = "{$expenseCount} expense(s)";
                        }
                        if ($notaDinasCount > 0) {
                            $details[] = "{$notaDinasCount} nota dinas detail(s)";
                        }
                        
                        Notification::make()
                            ->danger()
                            ->title('Deletion Not Allowed')
                            ->body("Vendor '{$record->name}' cannot be deleted because it is being used in " . implode(' and ', $details) . ". Please remove these associations first.")
                            ->persistent()
                            ->send();
                        return false;
                    }
                    
                    try {
                        $vendorName = $record->name ?? 'Unknown Vendor';
                        $record->delete();
                        
                        Notification::make()
                            ->success()
                            ->title('Vendor Successfully Deleted')
                            ->body("'{$vendorName}' has been deleted from the system.")
                            ->duration(5000)
                            ->send();
                            
                        // Redirect to index after successful deletion
                        return redirect($this->getResource()::getUrl('index'));
                        
                    } catch (\Illuminate\Database\QueryException $e) {
                        $errorCode = $e->getCode();
                        if ($errorCode === '23000') {
                            Notification::make()
                                ->danger()
                                ->title('Deletion Failed - Data Constraint')
                                ->body('This vendor cannot be deleted because it is referenced by other data in the system.')
                                ->persistent()
                                ->send();
                        } else {
                            Notification::make()
                                ->danger()
                                ->title('Database Error')
                                ->body('A database error occurred while deleting the vendor. Please try again later.')
                                ->persistent()
                                ->send();
                        }
                        return false;
                        
                    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                        Notification::make()
                            ->warning()
                            ->title('Vendor Already Deleted')
                            ->body('This vendor appears to have been already deleted by another user.')
                            ->send();
                        return false;
                        
                    } catch (\Exception $e) {
                        Log::error('Vendor deletion failed', [
                            'vendor_id' => $record->id ?? 'unknown',
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        
                        Notification::make()
                            ->danger()
                            ->title('Unexpected Error')
                            ->body('An unexpected error occurred while deleting the vendor. System administrator has been notified.')
                            ->persistent()
                            ->send();
                        return false;
                    }
                }),

            Actions\Action::make('cannot_delete')
                ->label('Cannot Delete')
                ->icon('heroicon-m-shield-exclamation')
                ->color('gray')
                ->tooltip('This vendor cannot be deleted because it is being used')
                ->visible(function (): bool {
                    $record = $this->getRecord();
                    if (!$record) return false;
                    
                    $productCount = $record->productVendors()->count();
                    $expenseCount = $record->vendors()->count();
                    $notaDinasCount = $record->notaDinasDetails()->count();
                    return $productCount > 0 || $expenseCount > 0 || $notaDinasCount > 0;
                })
                ->action(function () {
                    $record = $this->getRecord();
                    $productCount = $record->productVendors()->count();
                    $expenseCount = $record->vendors()->count();
                    $notaDinasCount = $record->notaDinasDetails()->count();
                    
                    $details = [];
                    if ($productCount > 0) {
                        $details[] = "{$productCount} product(s)";
                    }
                    if ($expenseCount > 0) {
                        $details[] = "{$expenseCount} expense(s)";
                    }
                    if ($notaDinasCount > 0) {
                        $details[] = "{$notaDinasCount} nota dinas detail(s)";
                    }
                    
                    Notification::make()
                        ->warning()
                        ->title('Cannot Delete Vendor')
                        ->body("'{$record->name}' cannot be deleted because it has associated " . implode(' and ', $details) . ". Please remove these associations first.")
                        ->persistent()
                        ->send();
                }),

            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['slug'] = Str::slug($data['name']);
        
        return $data;
    }

    // protected function getRedirectUrl(): string
    // {
    //     return $this->getResource()::getUrl('index');
    // }
}
