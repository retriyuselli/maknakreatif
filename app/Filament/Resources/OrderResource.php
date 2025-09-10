<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Widgets\UangDiterimaOverview; // <-- Tambahkan ini
use App\Enums\TransactionCategoryUangMasuk;
use App\Enums\OrderStatus;
use App\Enums\TransactionCategory;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\Widgets\OrderOverview;
use App\Filament\Resources\VendorResource;
use App\Filament\Resources\ProductResource;
use App\Models\Expense;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Prospect;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\RawJs;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Wizard;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationGroup = 'Finance';
    protected static ?string $navigationLabel = 'Project Wedding';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationIcon = 'heroicon-s-shopping-cart';
    protected static ?int $navigationSort = 1;

    /**
     * Safely convert any value to float for calculations
     */
    private static function safeFloatVal($value): float
    {
        if (is_null($value)) {
            return 0.0;
        }
        
        if (is_numeric($value)) {
            return floatval($value);
        }
        
        if (is_string($value)) {
            // Remove any non-numeric characters except dots and commas
            $cleaned = preg_replace('/[^\d.,]/', '', $value);
            // Remove commas (thousand separators)
            $cleaned = str_replace(',', '', $cleaned);
            // Handle empty string after cleaning
            if ($cleaned === '' || $cleaned === '.') {
                return 0.0;
            }
            return floatval($cleaned);
        }
        
        if (is_array($value)) {
            // If somehow we get an array, return 0
            Log::warning('Received array value in safeFloatVal', ['value' => $value]);
            return 0.0;
        }
        
        // Fallback for any other data type
        Log::warning('Unexpected data type in safeFloatVal', [
            'value' => $value,
            'type' => gettype($value)
        ]);
        return 0.0;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Wizard::make([
                Wizard\Step::make('Informasi Proyek')
                    ->icon('heroicon-o-information-circle')
                    ->description('Detail dasar proyek')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->default('MW-' . random_int(100000, 999999))
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->maxLength(32)
                            ->unique(Order::class, 'number', ignoreRecord: true),
                        Forms\Components\Select::make('prospect_id')
                            ->relationship('prospect', 'name_event', function (Builder $query) {
                                return $query->whereDoesntHave('orders', function ($query) {
                                    $query->whereNotNull('status');
                                });
                            })
                            ->searchable()
                            ->required()
                            ->unique(Order::class, 'prospect_id', ignoreRecord: true)
                            ->label('Prospek')
                            ->debounce(500)
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $prospect = Prospect::find($state);
                                    if ($prospect) {
                                        $set('name', $prospect->name_event);
                                        $set('slug', Str::slug($prospect->name_event));
                                    } else {
                                        $set('name', null);
                                        $set('slug', null);
                                    }
                                } else {
                                    $set('name', null);
                                    $set('slug', null);
                                }
                            })
                            ->disabled(fn (string $operation): bool => $operation === 'edit'),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->readOnly()
                            ->label('Nama Acara')
                            ->debounce(500),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->default(Auth::user()->id)
                            ->label('Manajer Akun'),
                        Forms\Components\TextInput::make('slug')
                            ->readOnly()->maxLength(255),
                        Forms\Components\Select::make('employee_id')
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->required()
                            ->label('Manajer Acara')
                            ->helperText('Jika belum ada isi dengan makna wedding'),
                        Forms\Components\TextInput::make('no_kontrak')
                            ->required()
                            ->label('No. Kontrak')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('pax')
                            ->required()
                            ->label('Pax')
                            ->default(1000)
                            ->numeric(),
                        Forms\Components\ToggleButtons::make('status')
                            ->inline()
                            ->options(OrderStatus::class)
                            ->label('Status Pesanan')
                            ->required(),
                        Forms\Components\RichEditor::make('note')
                            ->label('Keterangan Tambahan')
                            ->fileAttachmentsDirectory('orders')
                            ->fileAttachmentsDisk('public'),
                        Forms\Components\FileUpload::make('doc_kontrak')
                            ->label('Upload Kontrak')
                            ->reorderable()
                            ->required()
                            ->helperText('pastikan kontrak sudah semua ditanda tangani')
                            ->openable()
                            ->directory('doc_kontrak')
                            ->downloadable()
                            ->acceptedFileTypes(['application/pdf']),
                    ]),

                Wizard\Step::make('Detail Pembayaran')
                    ->icon('heroicon-o-currency-dollar')
                    ->description('Produk dan informasi pembayaran')
                    ->schema([
                        Forms\Components\Section::make('Product dipesan')
                            ->schema([self::getItemsRepeater()]),

                        Forms\Components\Section::make('Data Pembayaran')
                            ->schema([
                                Forms\Components\Repeater::make('Jika Ada Pembayaran')
                                    ->relationship('dataPembayaran')
                                    ->schema([
                                        Forms\Components\Grid::make(3)->schema([
                                            Forms\Components\TextInput::make('keterangan')
                                                ->label('Keterangan')
                                                ->prefix('Pembayaran')
                                                ->required()
                                                ->placeholder('1, 2, 3 dst'),
                                            Forms\Components\Select::make('payment_method_id')
                                                ->relationship('paymentMethod', 'name')
                                                ->getOptionLabelFromRecordUsing(fn ($record) => $record->is_cash ? 'Kas/Tunai' : ($record->bank_name ? "{$record->bank_name} - {$record->no_rekening}" : $record->name))
                                                ->required()
                                                ->label('Metode Pembayaran'),
                                            Forms\Components\TextInput::make('nominal')
                                                ->numeric()
                                                ->prefix('Rp. ')
                                                ->label('Nominal')
                                                ->required()
                                                ->mask(RawJs::make('$money($input)'))
                                                ->stripCharacters(',')
                                                ->debounce(800) // Perbesar debounce jika masih lambat
                                                ->lazy() // Hanya update saat form submit atau blur
                                                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                                    // Kalkulasi hanya untuk field ini jika diperlukan
                                                    if ($state !== null) {
                                                        self::updateDependentFinancialFields($get, $set);
                                                    }
                                                }),
                                            Forms\Components\Select::make('kategori_transaksi')
                                                ->options([
                                                    'uang_masuk' => 'Uang Masuk',
                                                    'uang_keluar' => 'Uang Keluar',
                                                ])
                                                ->default('uang_masuk')
                                                ->label('Tipe Transaksi')
                                                ->required(),
                                            Forms\Components\DatePicker::make('tgl_bayar')
                                                ->date()    
                                                ->required()
                                                ->label('Tgl. Bayar')
                                                ->live(onBlur: true), // Trigger hanya saat blur
                                            Forms\Components\FileUpload::make('image')
                                                ->label('Payment Proof')
                                                ->image()
                                                ->required()
                                                ->maxSize(1280)
                                                ->directory('payment-proofs/' . date('Y/m'))
                                                ->visibility('private')
                                                ->downloadable()
                                                ->openable() // Keep openable for both image and PDF
                                                ->acceptedFileTypes(['image/jpeg', 'image/png'])
                                                ->helperText('Max 1MB. JPG or PNG only.'),
                                        ]),
                                    ])
                                    // Gabung menjadi satu afterStateUpdated dan tambah debounce
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        // Ketika pembayaran berubah, hitung ulang field keuangan terkait
                                        self::updateDependentFinancialFields($get, $set);
                                    })
                                    ->collapsible()
                                    ->reorderable()
                                    ->cloneable()
                                    ->live(onBlur: true) // Ganti dari live() ke live(onBlur: true)
                                    ->itemLabel(
                                        fn(array $state): ?string => $state['keterangan'] ?? 'New Payment',
                                    ),
                        ]),
                        Forms\Components\TextInput::make('total_price')
                            ->numeric()
                            ->prefix('Rp. ')
                            ->label('Total Paket Awal')
                            ->readOnly()
                            ->default(0)
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(','),
                        Forms\Components\Hidden::make('is_cash')
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('promo')
                            ->default(0)
                            ->numeric()
                            ->prefix('Rp. ')
                            ->readOnly()
                            ->label('Promo')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->reactive()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                // Recalculate grand_total first with safe conversion
                                $total_price = self::safeFloatVal($get('total_price'));
                                $pengurangan_val = self::safeFloatVal($get('pengurangan'));
                                $promo_val = self::safeFloatVal($get('promo'));
                                $penambahan_val = self::safeFloatVal($get('penambahan'));
                                $grandTotal = $total_price + $penambahan_val - $promo_val - $pengurangan_val;
                                $set('grand_total', $grandTotal);
                                self::updateDependentFinancialFields($get, $set);
                            }),
                        Forms\Components\TextInput::make('penambahan')
                            ->default(0)
                            ->numeric()
                            ->prefix('Rp. ')
                            ->readOnly()
                            ->label('Penambahan (Coming Soon)')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->reactive()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                // Use safe conversion for all financial calculations
                                $total_price = self::safeFloatVal($get('total_price'));
                                $pengurangan_val = self::safeFloatVal($get('pengurangan'));
                                $promo_val = self::safeFloatVal($get('promo'));
                                $penambahan_val = self::safeFloatVal($get('penambahan'));
                                $grandTotal = $total_price + $penambahan_val - $promo_val - $pengurangan_val;
                                $set('grand_total', $grandTotal);
                                self::updateDependentFinancialFields($get, $set);
                            }),
                        Forms\Components\TextInput::make('pengurangan')
                            ->default(0)
                            ->numeric()
                            ->prefix('Rp. ')
                            ->label('Total Pengurangan dari Produk (Otomatis)')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->dehydrated() // pastikan field ini disimpan ke database
                            ->readOnly()
                            ->helperText('Nilai ini dihitung otomatis dari total pengurangan semua produk dalam order.'),
                    ]),

                Wizard\Step::make('Informasi Keuangan')
                    ->icon('heroicon-o-banknotes')
                    ->description('Catat detail keuangan')
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('bayar')
                                    ->label('Uang dibayar')
                                    ->readOnly()
                                    ->helperText('Pembayaran klien ke rek makna')
                                    ->default(0)
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(','),

                                Forms\Components\TextInput::make('grand_total')
                                    ->label('Grand Total')
                                    ->readOnly()
                                    ->helperText('Grand Total (Paket Awal - Pengurangan)')
                                    ->default(0)
                                    ->numeric()
                                    ->dehydrated(true)
                                    ->prefix('Rp')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(','),

                                Forms\Components\TextInput::make('tot_pengeluaran')
                                    ->label('Pengeluaran')
                                    ->readOnly()
                                    ->numeric()
                                    ->helperText('Total Pembayaran Ke Vendor')
                                    ->reactive()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->dehydrated(true)
                                    ->afterStateHydrated(function ($component, $state, $record) {
                                        if ($record) {
                                            $component->state($record->tot_pengeluaran);
                                        }
                                    }),

                                Forms\Components\TextInput::make('sisa')
                                    ->label('Sisa Pembayaran')
                                    ->readOnly()
                                    ->default(0)
                                    ->helperText('Sisa uang yang harus di bayar ke makna')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->dehydrated(true)
                                    ->afterStateHydrated(function ($component, $state, $record) {
                                        if ($record) {
                                            $component->state($record->sisa);
                                        }
                                    }),

                                Forms\Components\TextInput::make('laba_kotor')
                                    ->label('Laba Kotor')
                                    ->readOnly()
                                    ->numeric()
                                    ->helperText('Grand total - Pembayaran ke vendor')
                                    ->prefix('Rp')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->dehydrated(true)
                                    ->afterStateHydrated(function ($component, $state, $record) {
                                        if ($record) {
                                            $component->state($record->laba_kotor);
                                        }
                                    }),
                                Forms\Components\TextInput::make('uang_diterima')
                                    ->label('Uang Diterima')
                                    ->readOnly()
                                    ->default(0)
                                    ->helperText('Sisa uang yang diterima dari klien')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->dehydrated(true)
                                    ->afterStateHydrated(function ($component, $state, $record) {
                                        if ($record) {
                                            $component->state($record->uang_diterima);
                                        }
                                    }),
                            ])
                            ->columns(3),

                        Forms\Components\DatePicker::make('closing_date')
                            ->date()
                            ->label('Closing Date (Otomatis dari Pembayaran Pertama)')
                            ->readOnly() // Sebaiknya readOnly jika diisi otomatis
                            ->default(function (Get $get, ?Order $record): string {
                                // Saat form load (edit) atau jika ada record
                                if ($record && $record->exists) {
                                    $firstPayment = $record->dataPembayaran()->orderBy('tgl_bayar', 'asc')->first();
                                    if ($firstPayment && $firstPayment->tgl_bayar) {
                                        return Carbon::parse($firstPayment->tgl_bayar)->format('Y-m-d');
                                    }
                                }
                                // Saat create atau jika tidak ada pembayaran pada record yang ada
                                $paymentItems = $get('Jika Ada Pembayaran') ?? [];
                                if (!empty($paymentItems)) {
                                    // Urutkan pembayaran berdasarkan tgl_bayar
                                    usort($paymentItems, function ($a, $b) {
                                        return strtotime($a['tgl_bayar'] ?? 'now') <=> strtotime($b['tgl_bayar'] ?? 'now');
                                    });
                                    if (isset($paymentItems[0]['tgl_bayar']) && !empty($paymentItems[0]['tgl_bayar'])) {
                                        return Carbon::parse($paymentItems[0]['tgl_bayar'])->format('Y-m-d');
                                    }
                                }
                                return now()->format('Y-m-d'); // Fallback jika tidak ada data pembayaran atau saat create
                            }),

                        Forms\Components\Toggle::make('is_paid')
                            ->label('Lunas / Belum')
                            ->default(false)
                            ->disabled()
                            ->reactive()
                            ->live()
                            ->dehydrated()
                            ->onIcon('heroicon-m-bolt')
                            ->offIcon('heroicon-m-user')
                            ->helperText('Otomatis lunas jika sisa pembayaran > 0'),
                    ]),

                Wizard\Step::make('Pengeluaran')
                    ->icon('heroicon-o-book-open')
                    ->description('Catat detail pengeluaran')
                    ->schema([
                        Forms\Components\Section::make('Pengeluaran')
                            ->description('Catat pengeluaran ke vendor. Setiap vendor hanya boleh dipilih satu kali per order.')
                            ->schema([
                            Forms\Components\Repeater::make('expenses')
                                ->relationship('expenses')
                                ->live() // Enable live updates for anti-duplicate across items
                                ->schema([
                                    Forms\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\Select::make('nota_dinas_id')
                                                ->label('Nota Dinas')
                                                ->options(function (callable $get) {
                                                    $orderId = $get('../../id');

                                                    if (!$orderId) return [];

                                                    return \App\Models\NotaDinas::whereHas('details', function ($query) use ($orderId) {
                                                        $query->where('order_id', $orderId);
                                                    })->pluck('no_nd', 'id')->toArray();
                                                })
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                    // Only reset if nota_dinas_id is cleared or changed
                                                    if (!$state) {
                                                        $set('vendor_id', null);
                                                        $set('note', null);
                                                        $set('amount', null);
                                                        $set('account_holder', null);
                                                        $set('bank_name', null);
                                                        $set('bank_account', null);
                                                        $set('no_nd', null);
                                                    } else {
                                                        // Get the selected Nota Dinas and set no_nd field
                                                        $notaDinas = \App\Models\NotaDinas::find($state);
                                                        if ($notaDinas) {
                                                            $set('no_nd', $notaDinas->no_nd);
                                                        }
                                                    }
                                                }),

                                            Forms\Components\Hidden::make('no_nd')
                                                // ->readOnly()
                                                ->dehydrated()
                                                ->label('No. Nota Dinas'),
                                                // ->placeholder('Pilih Nota Dinas terlebih dahulu'),

                                            Forms\Components\Select::make('nota_dinas_detail_id')
                                                ->label('Detail Nota Dinas')
                                                ->options(function (callable $get) {
                                                    $notaDinasId = $get('nota_dinas_id');
                                                    if (!$notaDinasId) return [];

                                                    try {
                                                        // More robust path detection
                                                        $currentExpenseItems = $get('../../expenses') ?? $get('../expenses') ?? $get('expenses') ?? [];
                                                        $currentDetailId = $get('nota_dinas_detail_id');
                                                        $currentExpenseId = $get('id');
                                                        $orderId = $get('../../id') ?? $get('../id') ?? $get('id');
                                                        
                                                        // Get all used detail IDs more efficiently
                                                        $usedDetailIds = [];
                                                        
                                                        // From form state
                                                        foreach ($currentExpenseItems as $item) {
                                                            if (isset($item['nota_dinas_detail_id']) && 
                                                                $item['nota_dinas_detail_id'] !== $currentDetailId &&
                                                                (!isset($item['id']) || $item['id'] !== $currentExpenseId)) {
                                                                $usedDetailIds[] = $item['nota_dinas_detail_id'];
                                                            }
                                                        }
                                                        
                                                        // From database
                                                        if ($orderId) {
                                                            $dbUsedIds = \App\Models\Expense::where('order_id', $orderId)
                                                                ->whereNotNull('nota_dinas_detail_id')
                                                                ->when($currentExpenseId, function($query) use ($currentExpenseId) {
                                                                    return $query->where('id', '!=', $currentExpenseId);
                                                                })
                                                                ->pluck('nota_dinas_detail_id')
                                                                ->toArray();
                                                            
                                                            $usedDetailIds = array_unique(array_merge($usedDetailIds, $dbUsedIds));
                                                        }

                                        // Single optimized query with all conditions
                                        $availableDetails = \App\Models\NotaDinasDetail::with('vendor')
                                            ->where('nota_dinas_id', $notaDinasId)
                                            ->where('jenis_pengeluaran', 'wedding')
                                            ->whereNotIn('id', $usedDetailIds)
                                            ->whereHas('vendor') // More efficient than filter
                                            ->get();

                                        // Preserve current selection
                                        if ($currentDetailId && !$availableDetails->contains('id', $currentDetailId)) {
                                            $currentDetail = \App\Models\NotaDinasDetail::with('vendor')
                                                ->where('jenis_pengeluaran', 'wedding')
                                                ->find($currentDetailId);
                                            if ($currentDetail && $currentDetail->vendor) {
                                                $availableDetails->prepend($currentDetail);
                                            }
                                        }

                                                        return $availableDetails->mapWithKeys(function ($detail) use ($usedDetailIds) {
                                                            $vendorName = $detail->vendor->name ?? 'N/A';
                                                            $keperluan = $detail->keperluan ?? 'N/A';
                                                            $paymentStage = $detail->payment_stage ? " | {$detail->payment_stage}" : '';
                                                            $jumlah = number_format($detail->jumlah_transfer, 0, ',', '.');
                                                            
                                                            $usedIndicator = in_array($detail->id, $usedDetailIds) ? ' (Tersedia kembali)' : '';
                                                            
                                                            $label = "{$vendorName} | {$keperluan}{$paymentStage} | Rp {$jumlah}{$usedIndicator}";
                                                            return [$detail->id => $label];
                                                        })->toArray();
                                                        
                                                    } catch (\Exception $e) {
                                                        \Illuminate\Support\Facades\Log::error('Error in nota_dinas_detail_id options: ' . $e->getMessage(), [
                                                            'nota_dinas_id' => $notaDinasId,
                                                            'trace' => $e->getTraceAsString()
                                                        ]);
                                                        return [];
                                                    }
                                                })
                                                ->searchable()
                                                ->reactive()
                                                ->live()
                                                ->helperText(function (callable $get) {
                                                    try {
                                                        $notaDinasId = $get('nota_dinas_id');
                                                        if (!$notaDinasId) return 'Pilih Nota Dinas terlebih dahulu';
                                                        
                                                        // More robust path detection
                                                        $currentExpenseItems = $get('../../expenses') ?? $get('../expenses') ?? $get('expenses') ?? [];
                                                        $orderId = $get('../../id') ?? $get('../id') ?? $get('id');
                                                        
                                                        // Get actual used count (unique IDs)
                                                        $formUsedIds = array_filter(array_column($currentExpenseItems, 'nota_dinas_detail_id'));
                                                        $dbUsedIds = $orderId ? \App\Models\Expense::where('order_id', $orderId)
                                                            ->whereNotNull('nota_dinas_detail_id')
                                                            ->pluck('nota_dinas_detail_id')
                                                            ->toArray() : [];
                                                        
                                                        $allUsedIds = array_unique(array_merge($formUsedIds, $dbUsedIds));
                                                        $actualUsedCount = count($allUsedIds);
                                                        
                                                        $totalCount = \App\Models\NotaDinasDetail::where('nota_dinas_id', $notaDinasId)
                                                            ->where('jenis_pengeluaran', 'wedding')
                                                            ->count();
                                                        
                                                        return "Pilih detail nota dinas yang akan dibayar (Sudah dipilih: {$actualUsedCount}/{$totalCount})";
                                                        
                                                    } catch (\Exception $e) {
                                                        \Illuminate\Support\Facades\Log::warning('Error in helperText: ' . $e->getMessage());
                                                        return 'Pilih detail nota dinas yang akan dibayar';
                                                    }
                                                })
                                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                    try {
                                                        if (!$state) {
                                                            $set('vendor_id', null);
                                                            $set('account_holder', null);
                                                            $set('bank_name', null);
                                                            $set('bank_account', null);
                                                            $set('amount', null);
                                                            $set('note', null);
                                                            return;
                                                        }

                                                        // Fetch NotaDinasDetail and populate related fields
                                                        $notaDinasDetail = \App\Models\NotaDinasDetail::with('vendor')->find($state);
                                                        if ($notaDinasDetail) {
                                                            $set('vendor_id', $notaDinasDetail->vendor_id);
                                                            $set('account_holder', $notaDinasDetail->account_holder ?? $notaDinasDetail->vendor->account_holder);
                                                            $set('bank_name', $notaDinasDetail->bank_name ?? $notaDinasDetail->vendor->bank_name);
                                                            $set('bank_account', $notaDinasDetail->bank_account ?? $notaDinasDetail->vendor->bank_account);
                                                            $set('amount', self::safeFloatVal($notaDinasDetail->jumlah_transfer ?? 0));
                                                            $set('note', $notaDinasDetail->keperluan ?? null);
                                                        }
                                                    } catch (\Exception $e) {
                                                        \Illuminate\Support\Facades\Log::error('Error in afterStateUpdated: ' . $e->getMessage());
                                                    }
                                                })
                                                ->required()
                                                ->columnSpan(2),

                                            Forms\Components\Hidden::make('vendor_id'),
                                            
                                            ]),
                                            
                                            Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('bank_name')
                                                    ->label('Bank')
                                                    ->required()
                                                    ->live()
                                                    ->columnSpan(1),

                                                Forms\Components\TextInput::make('account_holder')
                                                    ->label('Nama Rekening')
                                                    ->required()
                                                    ->live()
                                                    ->columnSpan(1),
                                                
                                                Forms\Components\TextInput::make('bank_account')
                                                    ->label('Nomor Rekening')
                                                    ->required()
                                                    ->live()
                                                    ->columnSpan(1),
                                            ]),

                                        
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('amount')
                                                    ->label('Jumlah Transfer')
                                                    ->numeric()
                                                    ->prefix('Rp. ')
                                                    ->mask(RawJs::make('$money($input)'))
                                                    ->stripCharacters(',')
                                                    ->dehydrateStateUsing(fn ($state) => floatval(str_replace([',', '.'], ['', '.'], $state ?? 0)))
                                                    ->required(),

                                                Forms\Components\Select::make('payment_method_id')
                                                    ->label('Metode Pembayaran')
                                                    ->required()
                                                    ->options(\App\Models\PaymentMethod::all()
                                                    ->pluck('name', 'id')),

                                                Forms\Components\DatePicker::make('date_expense')
                                                    ->label('Tanggal Pengeluaran')
                                                    ->default(now())
                                                    ->required(),
                                            ]),

                                        Forms\Components\Grid::make(1)
                                            ->schema([
                                                Forms\Components\Textarea::make('note')
                                                    ->label('Catatan / Keperluan')
                                                    ->required()
                                                    ->rows(3)
                                                    ->columnSpan(1),
                                                Forms\Components\FileUpload::make('image')
                                                    ->label('Bukti Transfer')
                                                    ->directory('expense-proofs')
                                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                                    ->maxSize(5120) // 5MB
                                                    ->required()
                                                    ->columnSpan(1),
                                            ]),
                                ])
                                ->defaultItems(0)
                                ->collapsible()
                                ->collapsed(false)
                                ->itemLabel(function (array $state): ?string {
                                    if (!isset($state['vendor_id']) || !$state['vendor_id']) {
                                        return '🆕 Expense Baru';
                                    }
                                    
                                    try {
                                        $vendor = \App\Models\Vendor::find($state['vendor_id']);
                                        $vendorName = $vendor?->name ?? 'Vendor #' . $state['vendor_id'];
                                        
                                        // Safe currency formatting helper
                                        $formatCurrency = function($value) {
                                            if (empty($value)) return 'Rp 0';
                                            
                                            // Handle different data types
                                            if (is_string($value)) {
                                                // Remove existing formatting
                                                $value = preg_replace('/[^\d.,]/', '', $value);
                                                $value = str_replace(',', '', $value);
                                            }
                                            
                                            $numericValue = self::safeFloatVal($value);
                                            return 'Rp ' . number_format($numericValue, 0, ',', '.');
                                        };
                                        
                                        $formattedAmount = $formatCurrency($state['amount'] ?? 0);
                                        
                                        // Get payment stage label from NotaDinasDetail
                                        $paymentStage = 'DP'; // default
                                        if (isset($state['nota_dinas_detail_id'])) {
                                            try {
                                                $notaDinasDetail = \App\Models\NotaDinasDetail::find($state['nota_dinas_detail_id']);
                                                $paymentStage = $notaDinasDetail?->payment_stage ?? 'DP';
                                            } catch (\Exception $e) {
                                                // Keep default
                                            }
                                        }
                                        
                                        return "🏪 {$vendorName} ({$paymentStage}) - {$formattedAmount}";
                                    } catch (\Exception $e) {
                                        \Illuminate\Support\Facades\Log::warning('Error in expense itemLabel: ' . $e->getMessage());
                                        return '⚠️ Expense Item';
                                    }
                                })
                                ->addActionLabel('Tambah Expense')
                                ->reorderable()
                                ->cloneable()
                                ->afterStateUpdated(function ($state, $livewire) {
                                    // Force refresh form to update options in all items
                                    $livewire->dispatch('refreshForm');
                                }),
                        ]),
                    ]),

                // Wizard\Step::make('Pengeluaran')
                //     ->icon('heroicon-o-book-open')
                //     ->description('Catat detail pengeluaran')
                //     ->schema([
                //         Forms\Components\Section::make('Pengeluaran')->schema([
                //             Forms\Components\Repeater::make('expenses')
                //                 ->relationship('expenses')
                //                 ->schema([
                //                     Forms\Components\Grid::make(3)
                //                         ->schema([
                //                             Forms\Components\Select::make('vendor_id')
                //                                 ->relationship(
                //                                     name: 'vendor',
                //                                     titleAttribute: 'name',
                //                                     // Filter untuk hanya menampilkan vendor dengan status 'vendor'
                //                                     // modifyQueryUsing: fn (Builder $query) => $query->where('status', 'vendor')
                //                                 )
                //                                 ->required()
                //                                 ->searchable()
                //                                 ->preload()
                //                                 ->columnSpan(1),

                //                             Forms\Components\TextInput::make('no_nd')
                //                                 ->required()->prefix('ND-0')
                //                                 ->label('Nomor Nota Dinas')
                //                                 ->numeric()
                //                                 ->columnSpan(1),

                //                             Forms\Components\Select::make('kategori_transaksi')
                //                                 ->options([
                //                                     'uang_masuk' => 'Uang Masuk',
                //                                     'uang_keluar' => 'Uang Keluar',
                //                                 ])
                //                                 ->default('uang_keluar')
                //                                 ->label('Tipe Transaksi')
                //                                 ->required(),
                //                             Forms\Components\TextInput::make('note')
                //                                 ->required()
                //                                 ->label('Keterangan pembayaran')
                //                                 ->maxLength(255),

                //                             Forms\Components\DatePicker::make('date_expense')
                //                                 ->required()
                //                                 ->label('Tanggal pembayaran')
                //                                 ->columnSpan(1),

                //                             Forms\Components\TextInput::make('amount')
                //                                 ->required()
                //                                 ->label('Jumlah pembayaran')
                //                                 ->prefix('Rp. ')
                //                                 ->numeric()
                //                                 ->live()
                //                                 ->reactive()
                //                                 ->mask(RawJs::make('$money($input)'))
                //                                 ->stripCharacters(',')
                //                                 ->columnSpan(1),

                //                             Forms\Components\Select::make('payment_method_id')
                //                                 ->relationship('paymentMethod', 'name')
                //                                 ->getOptionLabelFromRecordUsing(fn ($record) => $record->is_cash ? 'Kas/Tunai' : ($record->bank_name ? "{$record->bank_name} - {$record->no_rekening}" : $record->name))
                //                                 ->label('Sumber Pembayaran')
                //                                 ->required()
                //                                 ->columnSpan(1),

                //                             Forms\Components\FileUpload::make('image')
                //                                 ->image()
                //                                 ->directory('invoice_expenses')
                //                                 ->acceptedFileTypes(['image/*', 'application/pdf']) // Allow images and PDFs
                //                                 ->label('Invoice')
                //                                 ->openable()
                //                                 ->downloadable()
                //                                 ->maxSize(1280)
                //                                 ->columnSpan(1),
                //                         ]),
                //                 ])
                //                 ->collapsible()
                //                 ->itemLabel(fn(array $state): ?string => $state['vendor_id'] ? Vendor::find($state['vendor_id'])?->name ?? 'Unnamed Vendor' : 'New Facility')
                //                 ->reorderable()
                //                 ->cloneable()
                //                 ->reactive()
                //                 ->live()
                //                 ->extraItemActions([ // Mengganti aksi untuk mengarah ke VendorResource
                //                     Action::make('openVendor')
                //                         ->label('Open Vendor')
                //                         ->icon('heroicon-m-building-storefront')
                //                         ->color('secondary')
                //                         ->url(function (array $arguments, Repeater $component): ?string {
                //                             $itemData = $component->getRawItemState($arguments['item']);
                //                             $vendorId = $itemData['vendor_id'] ?? null;
                //                             if (!$vendorId) {
                //                                 return null;
                //                             }
                //                             $vendor = Vendor::find($vendorId);
                //                             return $vendor ? VendorResource::getUrl('edit', ['record' => $vendor]) : null;
                //                         }, shouldOpenInNewTab: true)
                //                         ->hidden(function (array $arguments, Repeater $component): bool {
                //                             $itemData = $component->getRawItemState($arguments['item']);
                //                             return empty($itemData['vendor_id']);
                //                         })
                //                         ->tooltip('Buka data vendor ini di tab baru'),
                //                 ]),
                //         ]),
                //     ]),

                Wizard\Step::make('Riwayat Modifikasi')
                    ->icon('heroicon-o-clock')
                    ->description('Catat detail modifikasi')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created at')
                            ->content(fn(Order $record): ?string => $record->created_at?->diffForHumans()),
                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last modified at')
                            ->content(fn(Order $record): ?string => $record->updated_at?->diffForHumans()),
                        Forms\Components\Placeholder::make('last_edited_by')
                            ->label('Last Edited By')
                            ->content(fn(Order $record): ?string => $record->user?->name ?? 'Unknown User'),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn(?Order $record) => $record === null),
                ])
                ->columnSpan('full')
                ->columns(3)
                ->skippable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc') // Tambahkan baris ini
            ->poll('5s') // refresh data setiap 3 detik
            ->columns([
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'processing',
                        'danger' => 'cancelled',
                        'primary' => 'done',
                    ]),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Pembayaran')
                    ->getStateUsing(function (Order $record): string {
                        $paid = $record->bayar ?? 0;
                        $total = $record->grand_total ?? 0;

                        if ($total == 0) {
                            return '0%'; // Atau 'N/A' jika lebih sesuai
                        }

                        $percentage = min(round(($paid / $total) * 100), 100);
                        return $percentage . '%';
                    })
                    ->color(fn(Order $record): string => $record->is_paid ? 'success' : ($record->bayar > 0 ? 'warning' : 'danger'))
                    ->alignment(Alignment::Center)
                    ->badge()
                    ->toggleable(),

                // Order Identification
                Tables\Columns\TextColumn::make('number')
                    ->label('Nomor Pesanan')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()->copyable()->copyMessage('Nomor pesanan berhasil disalin')
                    ->sortable()->tooltip('Klik untuk menyalin nomor pesanan')
                    ->description(fn(Order $record): string => "No : {$record->no_kontrak}")
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('id')
                    ->label('SKU/ID'),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable()
                    ->copyMessage('Order slug copied successfully'),

                // Event Details
                Tables\Columns\TextColumn::make('prospect.name_event')
                    ->label('Nama Acara')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->copyable()
                    ->copyMessage('Nama acara berhasil disalin'),

                // Important Dates Group
                Tables\Columns\TextColumn::make('closing_date')
                    ->label('Tanggal Closing')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('prospect.date_lamaran')
                    ->label('Lamaran')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->date('d M Y')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('prospect.date_akad')
                    ->label('Akad')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->date('d M Y')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('prospect.date_resepsi')
                    ->label('Resepsi')
                    ->date('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->toggleable()
                    ->sortable(),

                // Team Information
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Manajer Acara')
                    ->searchable()
                    ->sortable()
                    ->color('success')
                    ->description(fn(Order $record): string => "MA: {$record->user?->name}"),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Grand Total')->money('IDR')
                    ->alignEnd()
                    ->description(fn(Order $record): string => $record->promo > 0 || $record->pengurangan > 0 ? 'Pengurangan: -' . number_format($record->promo + $record->pengurangan, 0, ',', '.') : '')->color('success'),

                Tables\Columns\TextColumn::make('bayar')
                    ->label('Jumlah Dibayar')
                    ->numeric()
                    ->money('IDR')
                    ->alignment(Alignment::Right)
                    ->color('success')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('sisa')
                    ->label('Sisa Tagihan')
                    ->numeric()
                    ->money('IDR')
                    ->alignment(Alignment::Right)
                    ->color('danger')
                    ->toggleable(),

                // Payment Status

                // Advanced Financial Details (Hidden by Default)
                Tables\Columns\TextColumn::make('tot_pengeluaran')
                    ->label('Total Pengeluaran')
                    ->numeric()
                    ->money('IDR')
                    ->alignment(Alignment::Right)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('laba_kotor')
                    ->label('Laba/Rugi')
                    ->numeric()
                    ->money('IDR')
                    ->alignment(Alignment::Right)
                    ->color(fn(Order $record) => $record->laba_kotor > 0 ? 'success' : 'danger')->toggleable(isToggledHiddenByDefault: true)->weight(FontWeight::Bold),

                // Additional Details (Hidden by Default)
                Tables\Columns\TextColumn::make('items.product.name')
                    ->label('Produk')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->listWithLineBreaks()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                // Date Range Filters
                Tables\Filters\Filter::make('event_dates')
                    ->form([
                        Forms\Components\Select::make('date_type')
                            ->label('Filter By Event')
                            ->options([
                                'all' => 'All Events',
                                'date_lamaran' => 'Lamaran Date',
                                'date_akad' => 'Akad Date',
                                'date_resepsi' => 'Reception Date',
                                // 'closing_date' => 'Closing Date',
                            ])
                            ->default('all')
                            ->required(),

                        Forms\Components\DatePicker::make('from_date')
                            ->label('From')
                            ->default(now()->startOfMonth())
                            ->displayFormat('d M Y'),

                        Forms\Components\DatePicker::make('until_date')
                            ->label('Until')
                            ->default(now()->endOfMonth())
                            ->displayFormat('d M Y'),
                    ])
                    ->columns(1)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['date_type'] && ($data['from_date'] || $data['until_date']), function (Builder $query) use ($data) {
                            return $query->whereHas('prospect', function ($query) use ($data) {
                                if ($data['date_type'] === 'all') {
                                    // For "All Events", use OR conditions to check all date fields
                                    $query->where(function ($subQuery) use ($data) {
                                        // Lamaran dates
                                        $subQuery->when($data['from_date'], function ($q) use ($data) {
                                            $q->orWhere(function ($q) use ($data) {
                                                $q->whereDate('date_lamaran', '>=', $data['from_date'])->when($data['until_date'], fn($q) => $q->whereDate('date_lamaran', '<=', $data['until_date']));
                                            });
                                        });

                                        // Akad dates
                                        $subQuery->when($data['from_date'], function ($q) use ($data) {
                                            $q->orWhere(function ($q) use ($data) {
                                                $q->whereDate('date_akad', '>=', $data['from_date'])->when($data['until_date'], fn($q) => $q->whereDate('date_akad', '<=', $data['until_date']));
                                            });
                                        });

                                        // Resepsi dates
                                        $subQuery->when($data['from_date'], function ($q) use ($data) {
                                            $q->orWhere(function ($q) use ($data) {
                                                $q->whereDate('date_resepsi', '>=', $data['from_date'])->when($data['until_date'], fn($q) => $q->whereDate('date_resepsi', '<=', $data['until_date']));
                                            });
                                        });

                                        // Closing dates
                                        // $subQuery->when($data['from_date'], function ($q) use ($data) {
                                        //     $q->orWhere(function ($q) use ($data) {
                                        //         $q->whereDate('closing_date', '>=', $data['from_date'])
                                        //           ->when($data['until_date'], fn ($q) =>
                                        //               $q->whereDate('closing_date', '<=', $data['until_date']));
                                        //     });
                                        // });
                                    });

                                    // Apply sorting for "All Events" - sort by the nearest event
                                    if ($data['sort_order'] ?? null) {
                                        $query->orderByRaw(
                                            "LEAST(
                                                COALESCE(date_lamaran, '9999-12-31'),
                                                COALESCE(date_akad, '9999-12-31'),
                                                COALESCE(date_resepsi, '9999-12-31')
                                            ) " . $data['sort_order'],
                                        );
                                    }
                                } else {
                                    // For specific event types
                                    $dateField = $data['date_type'];

                                    $query->when($data['from_date'], function ($q) use ($data, $dateField) {
                                        $q->whereDate($dateField, '>=', $data['from_date']);
                                    });

                                    $query->when($data['until_date'], function ($q) use ($data, $dateField) {
                                        $q->whereDate($dateField, '<=', $data['until_date']);
                                    });

                                    if ($data['sort_order'] ?? null) {
                                        $query->orderBy($dateField, $data['sort_order']);
                                    }
                                }

                                // Handle completed events
                                if (!($data['include_completed'] ?? true)) {
                                    if ($data['date_type'] === 'all') {
                                        $query->where(function ($q) {
                                            $now = now();
                                            $q->whereDate('date_lamaran', '>=', $now)->orWhereDate('date_akad', '>=', $now)->orWhereDate('date_resepsi', '>=', $now);
                                        });
                                    } else {
                                        $query->whereDate($data['date_type'], '>=', now());
                                    }
                                }
                            });
                        });
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['date_type'] ?? null) {
                            $eventType = match ($data['date_type']) {
                                'all' => 'All Events',
                                'date_lamaran' => 'Lamaran',
                                'date_akad' => 'Akad',
                                'date_resepsi' => 'Reception',
                                default => '',
                            };

                            if ($data['from_date'] ?? null) {
                                $indicators[] = 'From: ' . Carbon::parse($data['from_date'])->format('d M Y');
                            }

                            if ($data['until_date'] ?? null) {
                                $indicators[] = 'Until: ' . Carbon::parse($data['until_date'])->format('d M Y');
                            }

                            if (!empty($indicators)) {
                                array_unshift($indicators, $eventType);
                            }

                            if (!($data['include_completed'] ?? true)) {
                                $indicators[] = 'Upcoming Only';
                            }
                        }

                        return $indicators;
                    })
                    ->columnSpanFull(),

                // Filter baru: Order yang memiliki dokumen kontrak
                Tables\Filters\Filter::make('has_contract_document')
                    ->label('Has Contract Document')
                    ->query(fn (Builder $query) => $query->whereNotNull('doc_kontrak'))
                    ->toggle(), // Menggunakan toggle untuk filter on/off sederhana

                // Filter untuk order yang TIDAK memiliki dokumen kontrak (pending)
                Tables\Filters\Filter::make('no_contract_document')
                    ->label('No Contract Document')
                    ->query(fn (Builder $query) => $query->whereNull('doc_kontrak'))
                    ->toggle(),
                // Team Member Filters
                Tables\Filters\Filter::make('team')
                    ->form([Forms\Components\Select::make('employee_id')->label('Event Manager')->relationship('employee', 'name')->searchable()->preload(), Forms\Components\Select::make('user_id')->label('Account Manager')->relationship('user', 'name')->searchable()->preload()])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['employee_id'] ?? null, fn($query, $id) => $query->where('employee_id', $id))->when($data['user_id'] ?? null, fn($query, $id) => $query->where('user_id', $id));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['employee_id'] ?? null) {
                            $employee = \App\Models\Employee::find($data['employee_id']);
                            $indicators['em'] = 'EM: ' . ($employee?->name ?? 'Unknown');
                        }
                        if ($data['user_id'] ?? null) {
                            $user = \App\Models\User::find($data['user_id']);
                            $indicators['am'] = 'AM: ' . ($user?->name ?? 'Unknown');
                        }

                        return $indicators;
                    }),
                Tables\Filters\Filter::make('closing_date_filter')
                    ->form([
                        // Anda bisa membuat field ini terlihat jika ingin pengguna juga bisa memfilter manual
                        // Forms\Components\Select::make('year')
                        //     ->label('Closing Year')
                        //     ->options(Order::selectRaw('DISTINCT YEAR(closing_date) as year')->pluck('year', 'year')->sortDesc()),
                        // Forms\Components\Select::make('month')
                        //     ->label('Closing Month')
                        //     ->options(function () {
                        //         $months = [];
                        //         for ($m = 1; $m <= 12; $m++) {
                        //             $months[$m] = Carbon::create()->month($m)->format('F');
                        //         }
                        //         return $months;
                        //     }),
                        // Atau biarkan kosong jika hanya untuk URL
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['year']) && is_numeric($data['year'])) {
                            $query->whereYear('closing_date', (int)$data['year']);
                        }
                        if (isset($data['month']) && is_numeric($data['month'])) {
                            $monthNum = (int)$data['month'];
                            if ($monthNum >= 1 && $monthNum <= 12) {
                                $query->whereMonth('closing_date', $monthNum);
                            }
                        }
                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if (isset($data['year']) && $data['year'] !== '' && is_numeric($data['year'])) {
                            $indicators[] = 'Closing Year: ' . $data['year'];
                        }
                        if (isset($data['month']) && $data['month'] !== '' && is_numeric($data['month'])) {
                            $monthNum = (int) $data['month'];
                            if ($monthNum >= 1 && $monthNum <= 12) {
                                $indicators[] = 'Closing Month: ' . Carbon::create()->month($monthNum)->format('F');
                            }
                        }
                        return $indicators;
                    }),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->tooltip('Edit detail pesanan')
                        ->visible(fn (Order $record): bool => !$record->trashed()),

                    Tables\Actions\ViewAction::make()
                        ->tooltip('Lihat detail pesanan')
                        ->visible(fn (Order $record): bool => $record->trashed()),

                    Tables\Actions\RestoreAction::make()
                        ->tooltip('Pulihkan pesanan')
                        ->successNotificationTitle('Pesanan berhasil dipulihkan')
                        ->visible(fn (Order $record): bool => $record->trashed()),

                    Tables\Actions\DeleteAction::make()
                        ->tooltip('Hapus pesanan')
                        ->visible(fn (Order $record): bool => !$record->trashed())
                        ->action(function (Order $record) {
                            // Aturan bisnis: Mencegah penghapusan jika ada item atau pembayaran.
                            if ($record->items()->exists()) {
                                Notification::make()
                                    ->danger()
                                    ->title('Penghapusan Gagal')
                                    ->body("Pesanan '{$record->number}' tidak dapat dihapus karena memiliki item terkait.")
                                    ->send();
                                return;
                            }
                            
                            // Model event akan otomatis menghapus related records
                            $record->delete();
                            
                            Notification::make()
                                ->success()
                                ->title('Pesanan Dihapus')
                                ->body("Pesanan '{$record->number}' berhasil dihapus.")
                                ->send();
                        }),

                    Tables\Actions\ForceDeleteAction::make()
                        ->tooltip('Hapus permanen pesanan')
                        ->successNotificationTitle('Pesanan berhasil dihapus permanen')
                        ->modalHeading('Hapus Permanen Pesanan')
                        ->modalDescription('Apakah Anda yakin ingin menghapus pesanan ini secara permanen? Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait.')
                        ->modalSubmitActionLabel('Ya, hapus permanen')
                        ->visible(fn (Order $record): bool => $record->trashed())
                        ->requiresConfirmation()
                        ->action(function (Order $record) {
                            // Hapus data terkait secara manual jika diperlukan
                            $record->items()->forceDelete();
                            $record->dataPembayaran()->forceDelete();
                            $record->expenses()->forceDelete();
                            
                            // Hapus pesanan secara permanen
                            $record->forceDelete();
                            
                            Notification::make()
                                ->success()
                                ->title('Pesanan Dihapus Permanen')
                                ->body("Pesanan '{$record->number}' dan semua data terkait telah dihapus secara permanen.")
                                ->send();
                        }),

                    Tables\Actions\Action::make('Invoice Actions')
                        ->label('Aksi Invoice')
                        ->icon('heroicon-o-document-text')
                        ->color('primary')
                        ->url(fn($record)=>self::getUrl('invoice', ['record' => $record->id]))
                        ->visible(fn (Order $record): bool => !$record->trashed())
                ])
                    ->tooltip('Aksi Pesanan')
                    ->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus pesanan yang dipilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus pesanan yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, hapus')
                        ->action(function (EloquentCollection $records) {
                            $preventedDeletions = 0;
                            $deletedCount = 0;
                            $preventedOrderNumbers = [];

                            foreach ($records as $record) {
                                // Aturan bisnis: Mencegah penghapusan jika ada item.
                                if ($record->items()->exists()) {
                                    $preventedDeletions++;
                                    $preventedOrderNumbers[] = $record->number;
                                } else { 
                                    // Model event akan otomatis menghapus related records
                                    $record->delete();
                                    $deletedCount++;
                                }
                            }

                            if ($deletedCount > 0) {
                                Notification::make()
                                    ->success()
                                    ->title('Orders Deleted')
                                    ->body("Successfully deleted {$deletedCount} order(s).")
                                    ->send();
                            }

                            if ($preventedDeletions > 0) {
                                Notification::make()
                                    ->danger()
                                    ->title('Some Deletions Prevented')
                                    ->body("Could not delete {$preventedDeletions} order(s) due to existing items: " . implode(', ', $preventedOrderNumbers))
                                    ->persistent() // Make it persistent so user can read it
                                    ->send();
                            }
                        }),
                    
                    Tables\Actions\RestoreBulkAction::make(),

                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Permanen Pesanan')
                        ->modalDescription('Apakah Anda yakin ingin menghapus pesanan yang dipilih secara permanen? Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait.')
                        ->modalSubmitActionLabel('Ya, hapus permanen')
                        ->action(function (EloquentCollection $records) {
                            $deletedCount = 0;
                            
                            foreach ($records as $record) {
                                // Hapus data terkait secara manual
                                $record->items()->forceDelete();
                                $record->dataPembayaran()->forceDelete();
                                $record->expenses()->forceDelete();
                                
                                // Hapus pesanan secara permanen
                                $record->forceDelete();
                                $deletedCount++;
                            }

                            Notification::make()
                                ->success()
                                ->title('Pesanan Dihapus Permanen')
                                ->body("Berhasil menghapus {$deletedCount} pesanan secara permanen beserta semua data terkait.")
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('updateStatus')
                        ->label('Perbarui Status')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->modalHeading('Perbarui Status Pesanan')
                        ->modalDescription('Pilih status baru untuk pesanan yang dipilih.')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('New Status')
                                ->options(OrderStatus::class) // Menggunakan Enum OrderStatus Anda
                                ->required(),
                        ])
                        ->action(function (array $data, \Illuminate\Database\Eloquent\Collection $records) {
                            $records->each->update(['status' => $data['status']]);
                            Notification::make()
                                ->title('Orders Status Updated')
                                ->body("The status of {$records->count()} orders has been updated to {$data['status']}.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    // Anda bisa menambahkan aksi massal lainnya di sini, misalnya:
                    Tables\Actions\ExportBulkAction::make(),
                ])->label('Aksi Massal'),
            ])
            ->striped()
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50])
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateHeading('Tidak Ada Pesanan')
            ->emptyStateDescription('Tidak ada pesanan yang sesuai dengan kriteria Anda. Anda dapat membuat pesanan baru dengan mengklik tombol di bawah ini.')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Buat Pesanan')
                    ->icon('heroicon-o-plus')
                    ->url(static::getUrl('create'))
                    ->color('primary'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
                //
            ];
    }

    public static function getWidgets(): array
    {
        return [OrderOverview::class, UangDiterimaOverview::class];
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        return (string) $modelClass::where('status', 'processing')->count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['number', 'prospect.name_event', 'user.name', 'employee.name', 'user.name'];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'invoice' => Pages\Invoice::route('/{record}/invoice'),
        ];
    }

    /**
     * Override the base query to include soft-deleted records.
     * This allows the TrashedFilter to work correctly.
     *
     * @return Builder
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('items')
            ->relationship()
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->options(Product::query()->where('stock', '>', 1)->pluck('name', 'id'))
                    ->required()
                    ->reactive()
                    ->live() // Anda bisa menambahkan live() jika ingin update instan saat produk dipilih
                    ->afterStateHydrated(function (Forms\Set $set, Forms\Get $get, $state) {
                        $product = Product::find($state);
                        $set('unit_price', $product?->product_price ?? 0);
                        $set('stock', $product?->stock ?? 0);
                    })

                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $product = Product::find($state);
                        $set('unit_price', $product?->product_price ?? 0);
                        $set('stock', $product?->stock ?? 0);
                        $quantity = $get('quantity') ?? 1; // Get quantity or default to 1
                        $stock = $get('stock');
                        self::updateTotalPrice($get, $set);
                    })
                    ->distinct()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->columnSpan([
                        'md' => 5,
                    ])
                    ->searchable(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->default(1)
                    ->columnSpan([
                        'md' => 1,
                    ])
                    ->minValue(1)
                    ->required()
                    ->reactive()
                    // ->live() // Anda bisa menambahkan live() jika ingin update instan saat kuantitas diubah
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $stock = $get('stock');
                        if ($state > $stock) {
                            $set('quantity', $stock);
                            Notification::make()->title('Stock tidak mencukupi')->warning()->send();
                        }
                        self::updateTotalPrice($get, $set);
                    }),
                Forms\Components\TextInput::make('stock')
                    ->label('Stok')
                    ->disabled()
                    ->dehydrated()
                    ->numeric()
                    ->required()
                    ->columnSpan([
                        'md' => 1,
                    ]),
                Forms\Components\TextInput::make('unit_price')
                    ->label('Unit Price')
                    ->disabled()
                    ->dehydrated()
                    ->prefix('Rp. ')
                    ->numeric()
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->required()
                    ->columnSpan([
                        'md' => 3,
                    ]),
            ])
            ->collapsible()
            ->reorderable()
            ->cloneable()
            ->reactive()
            ->live()
            ->itemLabel(fn(array $state): ?string => Product::find($state['product_id'])?->name)
            ->extraItemActions([
                Action::make('openProduct')
                    ->tooltip('Open product')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(function (array $arguments, Repeater $component): ?string {
                        $itemData = $component->getRawItemState($arguments['item']);
                        $product = Product::find($itemData['product_id']);
                        if (!$product) {
                            return null;
                        }
                        return ProductResource::getUrl('edit', ['record' => $product]);
                    }, shouldOpenInNewTab: true)
                    ->hidden(fn(array $arguments, Repeater $component): bool => blank($component->getRawItemState($arguments['item'])['product_id'])),
            ])
            ->defaultItems(1)
            ->hiddenLabel()
            ->columns([
                'md' => 10,
            ])
            ->reactive() // Membuat repeater reaktif
            // ->live() // Anda bisa menambahkan live() jika ingin update instan saat item ditambah/dihapus
            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                // Logika ini akan dijalankan ketika item di repeater berubah (ditambah, dihapus, atau field reaktif di dalamnya berubah)
                // $get relatif terhadap parent dari repeater (dalam kasus ini, Wizard\Step 'Payment Details')
                $orderItems = $get('items') ?? []; // 'items' adalah nama repeater
                $calculatedProductPengurangan = 0;
                $calculatedTotalPrice = 0;

                if (is_array($orderItems)) {
                    foreach ($orderItems as $item) {
                        if (!empty($item['product_id']) && !empty($item['quantity'])) {
                            $product = Product::find($item['product_id']);
                            if ($product) {
                                // Akumulasi total pengurangan dari produk (kuantitas * pengurangan produk)
                                $calculatedProductPengurangan += $item['quantity'] * ($product->pengurangan ?? 0);
                                // Akumulasi total harga berdasarkan harga jual produk (kuantitas * harga produk)
                                $calculatedTotalPrice += $item['quantity'] * ($product->product_price ?? 0);
                            }
                        }
                    }
                }

                $set('pengurangan', $calculatedProductPengurangan); // Mengatur field 'pengurangan' di form Order
                $set('total_price', $calculatedTotalPrice); // Mengatur field 'total_price' di form Order

                // Hitung ulang grand_total berdasarkan nilai baru
                $penambahan = self::safeFloatVal($get('penambahan'));
                $promo = self::safeFloatVal($get('promo'));
                $grandTotal = $calculatedTotalPrice + $penambahan - $promo - $calculatedProductPengurangan;
                $set('grand_total', $grandTotal); // Mengatur field 'grand_total' di form Order
            });
    }

    protected static function updateTotalPrice(Forms\Get $get, Forms\Set $set): void
    {
        $selectedProducts = collect($get('items'))->filter(fn($item) => !empty($item['product_id']) && !empty($item['quantity']));

        $productIds = $selectedProducts->pluck('product_id')->unique()->filter()->toArray();

        // Fetch products from DB and key by ID for efficient lookup
        $productsFromDb = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $calculatedTotalPrice = 0;
        $calculatedProductPengurangan = 0;

        foreach ($selectedProducts as $item) {
            $productId = $item['product_id'];
            $quantity = self::safeFloatVal($item['quantity'] ?? 0);
            
            // Check if product exists in our fetched collection and has a price
            if (isset($productsFromDb[$productId]) && isset($productsFromDb[$productId]->price)) {
                $productPrice = self::safeFloatVal($productsFromDb[$productId]->product_price ?? 0);
                $productPengurangan = self::safeFloatVal($productsFromDb[$productId]->pengurangan ?? 0);
                
                $calculatedTotalPrice += $productPrice * $quantity;
                $calculatedProductPengurangan += $productPengurangan * $quantity;
            }
        }

        $set('total_price', $calculatedTotalPrice);
        $set('pengurangan', $calculatedProductPengurangan); // Set field 'pengurangan'

        // Recalculate grand_total
        $penambahan = self::safeFloatVal($get('penambahan'));
        $promo = self::safeFloatVal($get('promo'));
        // Gunakan $calculatedProductPengurangan yang baru dihitung
        $grandTotal = $calculatedTotalPrice + $penambahan - $promo - $calculatedProductPengurangan;
        $set('grand_total', $grandTotal);

        // Panggil method baru untuk update sisa dan is_paid
        self::updateDependentFinancialFields($get, $set);
    }

    protected static function updateExchangePaid(Forms\Get $get, Forms\Set $set): void
    {
        $paidAmount = (int) $get('paid_amount') ?? 0;
        $totalPrice = (int) $get('total_price') ?? 0;
        $promoPrice = (int) $get('promo') ?? 0;
        $penambahanPrice = (int) $get('penambahan') ?? 0;
        $penguranganPrice = (int) $get('pengurangan') ?? 0;
        $exchangePaid = $totalPrice - $paidAmount - $promoPrice - $penguranganPrice + $penambahanPrice;
        $set('change_amount', $exchangePaid);
    }

    protected static function updateDependentFinancialFields(Forms\Get $get, Forms\Set $set): void
    {
        // Pertama, pastikan grand_total dihitung ulang dengan safe conversion
        $total_price = self::safeFloatVal($get('total_price'));
        $pengurangan_val = self::safeFloatVal($get('pengurangan'));
        $promo_val = self::safeFloatVal($get('promo'));
        $penambahan_val = self::safeFloatVal($get('penambahan'));
        $grandTotal = $total_price + $penambahan_val - $promo_val - $pengurangan_val;
        $set('grand_total', $grandTotal);

        // Hitung 'bayar' dari repeater 'dataPembayaran' dengan safe conversion
        $paymentItems = $get('Jika Ada Pembayaran') ?? [];
        $bayar = 0;
        if (is_array($paymentItems)) {
            foreach ($paymentItems as $paymentItem) {
                $nominalValue = $paymentItem['nominal'] ?? 0;
                $bayar += self::safeFloatVal($nominalValue);
            }
        }
        $set('bayar', $bayar);

        // Hitung 'sisa'
        $sisa = $grandTotal - $bayar;
        $set('sisa', $sisa);

        // Update 'is_paid'
        $set('is_paid', $sisa <= 0);

        // Update 'closing_date' based on the first payment date
        self::updateClosingDate($get, $set);
    }

    protected static function updateClosingDate(Forms\Get $get, Forms\Set $set): void
    {
        $paymentItems = $get('Jika Ada Pembayaran') ?? [];
        if (!empty($paymentItems)) {
            // Urutkan pembayaran berdasarkan tgl_bayar untuk mendapatkan yang paling awal
            usort($paymentItems, function ($a, $b) {
                return strtotime($a['tgl_bayar'] ?? 'now') <=> strtotime($b['tgl_bayar'] ?? 'now');
            });
            if (isset($paymentItems[0]['tgl_bayar']) && !empty($paymentItems[0]['tgl_bayar'])) {
                $set('closing_date', Carbon::parse($paymentItems[0]['tgl_bayar'])->format('Y-m-d'));
                return; // Keluar setelah menemukan tanggal pembayaran pertama
            }
        }
        // Jika tidak ada pembayaran, bisa di-set ke default atau dibiarkan (tergantung kebutuhan)
        // $set('closing_date', now()->format('Y-m-d')); // Atau biarkan saja jika tidak ada pembayaran
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Total proyek yang sedang diproses';
    }
}
