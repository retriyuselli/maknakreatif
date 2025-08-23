<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Alignment;
use Carbon\Carbon;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?string $title = 'Managed Projects';

    protected static ?string $icon = 'heroicon-o-clipboard-document-list';

    /**
     * Helper function to safely format dates
     */
    private function formatDateSafely($date, $format = 'd M Y'): string
    {
        if (empty($date)) {
            return '-';
        }
        
        if ($date instanceof \Carbon\Carbon || $date instanceof \DateTime) {
            return $date->format($format);
        }
        
        try {
            return Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return 'Invalid date';
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Project Information')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->label('Project Number')
                            ->readOnly(),

                        Forms\Components\Select::make('prospect_id')
                            ->relationship('prospect', 'name_event')
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->readOnly(),

                        Forms\Components\Select::make('status')
                            ->options(OrderStatus::class)
                            ->required(),

                        Forms\Components\DatePicker::make('closing_date')
                            ->label('Closing Date')
                            ->required(),

                        Forms\Components\TextInput::make('pax')
                            ->label('Number of Attendees')
                            ->numeric()
                            ->required(),

                        Forms\Components\Toggle::make('is_paid')
                            ->label('Payment Status')
                            ->onIcon('heroicon-m-check-circle')
                            ->offIcon('heroicon-m-clock')
                            ->onColor('success')
                            ->offColor('danger'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Financial Overview')
                    ->schema([
                        Forms\Components\TextInput::make('total_price')
                            ->label('Total Package Price')
                            ->prefix('Rp')
                            ->disabled(),

                        Forms\Components\TextInput::make('grand_total')
                            ->label('Final Price')
                            ->prefix('Rp')
                            ->disabled(),

                        Forms\Components\TextInput::make('bayar')
                            ->label('Amount Paid')
                            ->prefix('Rp')
                            ->disabled(),

                        Forms\Components\TextInput::make('sisa')
                            ->label('Outstanding Balance')
                            ->prefix('Rp')
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('number')
            ->columns([
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'processing',
                        'danger' => 'cancelled',
                        'primary' => 'done',
                    ]),
                
                Tables\Columns\TextColumn::make('number')
                    ->label('Project #')
                    ->searchable()
                    ->copyable()
                    ->sortable()
                    ->weight(FontWeight::Bold),
                
                Tables\Columns\TextColumn::make('prospect.name_event')
                    ->label('Event Name')
                    ->searchable()
                    ->wrap()
                    ->limit(30)
                    ->tooltip(fn (Order $record): string => 
                        $record->prospect?->name_event ?? ''),
                
                Tables\Columns\TextColumn::make('event_dates')
                    ->label('Event Dates')
                    ->getStateUsing(function (Order $record): string {
                        if (!$record->prospect) {
                            return 'No dates set';
                        }
                        
                        $dates = [];
                        
                        if ($record->prospect->date_lamaran) {
                            $formattedDate = $this->formatDateSafely($record->prospect->date_lamaran, 'd M');
                            $dates[] = "Engagement: {$formattedDate}";
                        }
                        
                        if ($record->prospect->date_akad) {
                            $formattedDate = $this->formatDateSafely($record->prospect->date_akad, 'd M');
                            $dates[] = "Ceremony: {$formattedDate}";
                        }
                        
                        if ($record->prospect->date_resepsi) {
                            $formattedDate = $this->formatDateSafely($record->prospect->date_resepsi, 'd M Y');
                            $dates[] = "Reception: {$formattedDate}";
                        }
                        
                        return !empty($dates) ? implode("\n", $dates) : 'No dates set';
                    })
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('closing_date')
                    ->label('Closing Date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Project Value')
                    ->money('IDR')
                    ->sortable()
                    ->alignment(Alignment::Right),
                
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment')
                    ->getStateUsing(function (Order $record): string {
                        $paid = $record->bayar ?? 0;
                        $total = $record->grand_total ?? 0;
                        
                        if ($total == 0) {
                            return '0%';
                        }
                        
                        $percentage = min(round(($paid / $total) * 100), 100);
                        return $percentage . '%';
                    })
                    ->color(fn (Order $record): string => 
                        $record->is_paid 
                            ? 'success'
                            : ($record->bayar > 0 ? 'warning' : 'danger')
                    )
                    ->alignment(Alignment::Center)
                    ->badge(),
                
                Tables\Columns\IconColumn::make('is_paid')
                    ->label('Paid')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(OrderStatus::class)
                    ->multiple(),
                
                Tables\Filters\Filter::make('event_date')
                    ->form([
                        Forms\Components\Select::make('date_type')
                            ->label('Event Type')
                            ->options([
                                'closing_date' => 'Closing Date',
                                'date_resepsi' => 'Reception Date',
                                'date_akad' => 'Ceremony Date',
                                'date_lamaran' => 'Engagement Date',
                            ])
                            ->default('closing_date'),
                            
                        Forms\Components\DatePicker::make('from_date')
                            ->label('From'),
                            
                        Forms\Components\DatePicker::make('until_date')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $dateField = $data['date_type'] ?? 'closing_date';
                        
                        if ($dateField === 'closing_date') {
                            return $query
                                ->when(
                                    $data['from_date'] ?? null,
                                    fn (Builder $query, $date): Builder => $query->whereDate('closing_date', '>=', $date),
                                )
                                ->when(
                                    $data['until_date'] ?? null,
                                    fn (Builder $query, $date): Builder => $query->whereDate('closing_date', '<=', $date),
                                );
                        } else {
                            return $query
                                ->when(
                                    $data['from_date'] ?? null,
                                    fn (Builder $query, $date): Builder => $query->whereHas(
                                        'prospect', 
                                        fn (Builder $query) => $query->whereDate($dateField, '>=', $date)
                                    ),
                                )
                                ->when(
                                    $data['until_date'] ?? null,
                                    fn (Builder $query, $date): Builder => $query->whereHas(
                                        'prospect', 
                                        fn (Builder $query) => $query->whereDate($dateField, '<=', $date)
                                    ),
                                );
                        }
                    }),
                
                Tables\Filters\Filter::make('payment_status')
                    ->form([
                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'paid' => 'Fully Paid',
                                'partial' => 'Partially Paid',
                                'unpaid' => 'Unpaid',
                            ])
                            ->required(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['payment_status'] ?? null) {
                            'paid' => $query->where('is_paid', true),
                            'partial' => $query->where('is_paid', false)->whereRaw('bayar > 0'),
                            'unpaid' => $query->whereRaw('COALESCE(bayar, 0) = 0'),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->url(fn (Order $record): string => 
                            route('filament.admin.resources.orders.edit', ['record' => $record])),
                    
                    Tables\Actions\Action::make('generate_invoice')
                        ->label('Generate Invoice')
                        ->icon('heroicon-o-document-text')
                        ->url(fn (Order $record): string => 
                            route('invoice.show', $record))
                        ->openUrlInNewTab()
                        ->color('success'),
                ])
                ->size('sm'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('update_status')
                        ->label('Update Status')
                        ->icon('heroicon-o-check-circle')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('New Status')
                                ->options(OrderStatus::class)
                                ->required(),
                        ])
                        ->action(function (array $data, $records): void {
                            foreach ($records as $record) {
                                $record->update(['status' => $data['status']]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('view_all_projects')
                    ->label('View All Projects')
                    ->url(fn (): string => route('filament.admin.resources.orders.index'))
                    ->icon('heroicon-o-clipboard-document-list')
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('closing_date', 'desc')
            ->poll('60s');
    }
}