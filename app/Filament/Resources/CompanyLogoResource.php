<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyLogoResource\Pages;
use App\Filament\Resources\CompanyLogoResource\RelationManagers;
use App\Models\CompanyLogo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyLogoResource extends Resource
{
    protected static ?string $model = CompanyLogo::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Company Logos';

    protected static ?string $pluralModelLabel = 'Company Logos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Company Information')
                    ->schema([
                        Forms\Components\TextInput::make('company_name')
                            ->required()
                            ->maxLength(255)
                            ->label('Company Name'),
                        Forms\Components\TextInput::make('website_url')
                            ->url()
                            ->maxLength(255)
                            ->label('Website URL')
                            ->placeholder('https://example.com'),
                        Forms\Components\FileUpload::make('logo_path')
                            ->label('Company Logo')
                            ->image()
                            ->directory('company-logos')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/svg+xml', 'image/webp'])
                            ->maxSize(2048)
                            ->hint('Recommended size: 200x100px, Max: 2MB'),
                        Forms\Components\TextInput::make('alt_text')
                            ->maxLength(255)
                            ->label('Alt Text')
                            ->hint('Alternative text for the logo'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Display Settings')
                    ->schema([
                        Forms\Components\Select::make('category')
                            ->required()
                            ->options([
                                'client' => 'Client',
                                'partner' => 'Partner',
                                'vendor' => 'Vendor',
                                'sponsor' => 'Sponsor',
                            ])
                            ->default('client'),
                        Forms\Components\Select::make('partnership_type')
                            ->required()
                            ->options([
                                'free' => 'Free',
                                'premium' => 'Premium',
                                'enterprise' => 'Enterprise',
                            ])
                            ->default('free'),
                        Forms\Components\TextInput::make('display_order')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->label('Display Order')
                            ->hint('Lower numbers appear first'),
                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->default(true)
                            ->label('Active'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('contact_email')
                            ->email()
                            ->maxLength(255)
                            ->label('Contact Email'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->size(60)
                    ->circular(),
                Tables\Columns\TextColumn::make('company_name')
                    ->searchable()
                    ->sortable()
                    ->label('Company Name'),
                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'primary' => 'client',
                        'success' => 'partner',
                        'warning' => 'vendor',
                        'danger' => 'sponsor',
                    ]),
                Tables\Columns\BadgeColumn::make('partnership_type')
                    ->colors([
                        'secondary' => 'free',
                        'warning' => 'premium',
                        'success' => 'enterprise',
                    ])
                    ->label('Type'),
                Tables\Columns\TextColumn::make('display_order')
                    ->numeric()
                    ->sortable()
                    ->label('Order'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('website_url')
                    ->searchable()
                    ->limit(30)
                    ->label('Website')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('contact_email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Contact'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'client' => 'Client',
                        'partner' => 'Partner',
                        'vendor' => 'Vendor',
                        'sponsor' => 'Sponsor',
                    ]),
                Tables\Filters\SelectFilter::make('partnership_type')
                    ->options([
                        'free' => 'Free',
                        'premium' => 'Premium',
                        'enterprise' => 'Enterprise',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('display_order');
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
            'index' => Pages\ListCompanyLogos::route('/'),
            'create' => Pages\CreateCompanyLogo::route('/create'),
            'edit' => Pages\EditCompanyLogo::route('/{record}/edit'),
        ];
    }
}
