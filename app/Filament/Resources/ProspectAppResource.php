<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProspectAppResource\Pages;
use App\Models\ProspectApp;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;

class ProspectAppResource extends Resource
{
    protected static ?string $model = ProspectApp::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Prospect Applications';
    protected static ?string $modelLabel = 'Prospect Application';
    protected static ?string $pluralModelLabel = 'Prospect Applications';
    protected static ?string $navigationGroup = 'Lead Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Section::make('Contact Information')
                ->description('Enter the applicant\'s contact details')
                ->icon('heroicon-o-user')
                ->schema([
                    TextInput::make('full_name')
                        ->label('Full Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g., John Doe')
                        ->autofocus(),

                    TextInput::make('email')
                        ->label('Email Address')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->placeholder('e.g., john.doe@example.com'),

                    TextInput::make('phone')
                        ->label('Phone Number')
                        ->tel()
                        ->required()
                        ->maxLength(20)
                        ->placeholder('e.g., +6281234567890')
                        ->prefix('+62'),

                    TextInput::make('position')
                        ->label('Job Position')
                        ->maxLength(255)
                        ->placeholder('e.g., Marketing Manager')
                        ->helperText('Optional'),
                ])
                ->columns(2),

            Section::make('Company Information')
                ->description('Provide details about the applicant\'s company')
                ->icon('heroicon-o-building-office-2')
                ->schema([
                    TextInput::make('company_name')
                        ->label('Company Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g., Acme Corp'),

                    Select::make('industry_id')
                        ->label('Industry')
                        ->relationship('industry', 'industry_name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select an industry')
                        ->helperText('Select the industry that best describes the company'),

                    TextInput::make('name_of_website')
                        ->label('Website/Domain')
                        ->maxLength(255)
                        ->placeholder('e.g., www.example.com')
                        ->url()
                        ->helperText('Optional'),

                    Select::make('user_size')
                        ->label('Company Size')
                        ->options([
                            '1-10' => '1-10 employees',
                            '11-50' => '11-50 employees',
                            '51-200' => '51-200 employees',
                            '201-500' => '201-500 employees',
                            '501-1000' => '501-1000 employees',
                            '1000+' => '1000+ employees',
                        ])
                        ->placeholder('Select company size')
                        ->helperText('Approximate number of employees'),
                ])
                ->columns(2),

            Section::make('Application Details')
                ->description('Details about the application and desired services')
                ->icon('heroicon-o-clipboard-document-list')
                ->schema([
                    Textarea::make('reason_for_interest')
                        ->label('Reason for Interest')
                        ->rows(3)
                        ->maxLength(1000)
                        ->placeholder('Explain why you are interested in our services'),

                    Select::make('status')
                        ->label('Application Status')
                        ->options([
                            'pending' => 'Pending Review',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                        ])
                        ->default('pending')
                        ->required(),

                    Select::make('service')
                        ->label('Service Package')
                        ->options([
                            'basic' => 'Basic Package - Coming Soon',
                            'standard' => 'Standard Package - Rp 8,500,000', 
                            'premium' => 'Premium Package - Coming Soon',
                            'enterprise' => 'Enterprise Package - Coming Soon',
                        ])
                        ->placeholder('Select service package')
                        ->helperText('Choose the service package that best fits your needs')
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            // Auto-update harga and bayar based on selected service
                            match ($state) {
                                'standard' => [
                                    $set('harga', 8500000),
                                    $set('bayar', 8500000)
                                ],
                                'basic', 'premium', 'enterprise' => [
                                    $set('harga', null),
                                    $set('bayar', null)
                                ],
                                default => [
                                    $set('harga', null),
                                    $set('bayar', null)
                                ],
                            };
                        }),

                    TextInput::make('harga')
                        ->label('Estimated Budget')
                        ->numeric()
                        ->prefix('Rp ')
                        ->placeholder('Price will be set automatically based on package')
                        ->helperText('Budget will be auto-filled when you select a service package')
                        ->dehydrated()
                        ->readOnly(),
                        
                    DatePicker::make('tgl_bayar')
                        ->label('Payment Date')
                        ->displayFormat('d M Y')
                        ->helperText('If a payment has been made, specify the date'),

                    TextInput::make('bayar')
                        ->label('Amount Paid')
                        ->numeric()
                        ->prefix('Rp ')
                        ->helperText('If a payment has been made, specify the amount')
                        ->dehydrated(),

                    RichEditor::make('notes')
                        ->label('Internal Notes')
                        ->placeholder('Add any internal notes or comments'),

                    DateTimePicker::make('submitted_at')
                        ->label('Submission Date & Time')
                        ->default(now())
                        ->displayFormat('M j, Y H:i'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Full Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),

                TextColumn::make('company_name')
                    ->label('Company')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('industry.industry_name')
                    ->label('Industry')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-phone')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('position')
                    ->label('Position')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('service')
                    ->label('Service Package')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('harga')
                    ->label('Budget')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('bayar')
                    ->label('Paid Amount')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tgl_bayar')
                    ->label('Payment Date')
                    ->date('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('user_size')
                    ->label('Company Size')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('M j, Y')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->placeholder('All Statuses'),

                SelectFilter::make('industry')
                    ->relationship('industry', 'industry_name')
                    ->searchable()
                    ->preload()
                    ->placeholder('All Industries'),

                SelectFilter::make('user_size')
                    ->label('Company Size')
                    ->options([
                        '1-10' => '1-10 employees',
                        '11-50' => '11-50 employees',
                        '51-200' => '51-200 employees',
                        '201-500' => '201-500 employees',
                        '501-1000' => '501-1000 employees',
                        '1000+' => '1000+ employees',
                    ])
                    ->placeholder('All Sizes'),

                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->color('info'),
                    
                    EditAction::make()
                        ->color('warning'),
                    
                    Action::make('generateProposal')
                        ->label('Generate Proposal')
                        ->icon('heroicon-o-document-text')
                        ->color('success')
                        ->url(fn (ProspectApp $record): string => route('prospect-app.proposal.pdf', $record))
                        ->openUrlInNewTab(),
                    
                    DeleteAction::make(),
                ])
                ->label('Actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
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
            'index' => Pages\ListProspectApps::route('/'),
            'create' => Pages\CreateProspectApp::route('/create'),
            'view' => Pages\ViewProspectApp::route('/{record}'),
            'edit' => Pages\EditProspectApp::route('/{record}/edit'),
        ];
    }

    
}
