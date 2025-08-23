<?php

namespace App\Filament\Resources\SimulasiProdukResource\Pages;

use App\Filament\Resources\SimulasiProdukResource;
use App\Models\SimulasiProduk;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Exceptions\Halt;
use Barryvdh\DomPDF\Facade\Pdf;

class ViewSimulasiInvoice extends ViewRecord
{
    protected static string $resource = SimulasiProdukResource::class;

    protected static string $view = 'filament.resources.simulasi-produk-resource.pages.view-simulasi-invoice';
    
    public $items = [];
    public $subtotal = 0;
    public $promo = 0;
    public $penambahan = 0;
    public $pengurangan = 0;
    public $grandTotal = 0;
    
    public function mount(int|string $record): void
    {
        parent::mount($record);
        
        // Adjust to get items from the related Product model
        if ($this->record->product) {
            // Assuming Product model has an 'items' relationship (e.g., to ProductVendor)
            $this->items = $this->record->product->items()->with('vendor')->get();
        } else {
            $this->items = collect(); // Empty collection if no product is linked
        }
        $this->subtotal = $this->record->total_price;
        $this->promo = $this->record->promo;
        $this->penambahan = $this->record->penambahan;
        $this->pengurangan = $this->record->pengurangan;
        $this->grandTotal = $this->record->grand_total;
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download_pdf')
                ->label('Download PDF')
                ->color('warning')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn () => route('simulasi.invoice.pdf', $this->record))
                ->openUrlInNewTab(),
                
            Actions\Action::make('back')
                ->label('Back')
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
                ->url(fn () => SimulasiProdukResource::getUrl('index')),
                
            Actions\Action::make('edit')
                ->label('Edit Simulation')
                ->color('success')
                ->icon('heroicon-o-pencil')
                ->url(fn () => SimulasiProdukResource::getUrl('edit', ['record' => $this->record])),
                
            Actions\Action::make('create_order')
                ->label('Create Order')
                ->color('primary')
                ->icon('heroicon-o-shopping-cart')
                ->action(function () {
                    try {
                        $order = $this->record->createOrder();
                        
                        // Show success notification
                        \Filament\Notifications\Notification::make()
                            ->title('Order Created Successfully')
                            ->body("Order #{$order->id} has been created from this simulation.")
                            ->success()
                            ->send();
                            
                        // Redirect using the correct method
                        $this->redirectRoute('filament.admin.resources.orders.edit', ['record' => $order->id]);
                    } catch (Halt $exception) {
                        return;
                    }
                })
                ->requiresConfirmation()
                ->modalHeading('Create Order from This Simulation')
                ->modalDescription('Are you sure you want to create a new order based on this simulation? All items and prices will be copied to the new order.')
                ->modalSubmitActionLabel('Yes, Create Order'),
        ];
    }
    
    public function getTitle(): string 
    {
        return 'Invoice: ' . $this->record->name;
    }
}
