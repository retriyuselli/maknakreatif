<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogResource\Pages;
use App\Filament\Resources\BlogResource\RelationManagers;
use App\Models\Blog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BlogResource extends Resource
{
    protected static ?string $model = Blog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationGroup = 'Content Management';
    
    protected static ?string $modelLabel = 'Blog Article';
    
    protected static ?string $pluralModelLabel = 'Blog Articles';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Information')
                    ->description('Main article information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $context, $state, callable $set) => $context === 'create' ? $set('slug', Str::slug($state)) : null)
                                    ->columnSpan(2),
                                
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Blog::class, 'slug', ignoreRecord: true)
                                    ->helperText('URL-friendly version of the title')
                                    ->columnSpan(2),
                            ]),
                        
                        Forms\Components\Textarea::make('excerpt')
                            ->required()
                            ->maxLength(500)
                            ->helperText('Brief description of the article (max 500 characters)')
                            ->columnSpanFull(),
                    ]),

                Section::make('Content')
                    ->description('Article content and media')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('featured_image')
                            ->url()
                            ->placeholder('https://example.com/image.jpg')
                            ->helperText('Featured image URL (use Unsplash or other image sources)')
                            ->columnSpanFull(),
                    ]),

                Section::make('Categorization')
                    ->description('Category and tags')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('category')
                                    ->required()
                                    ->options([
                                        'Featured' => 'Featured',
                                        'Tutorial' => 'Tutorial',
                                        'Business' => 'Business',
                                        'Tips' => 'Tips',
                                        'Keuangan' => 'Keuangan',
                                    ])
                                    ->searchable()
                                    ->preload(),
                                
                                Forms\Components\TagsInput::make('tags')
                                    ->placeholder('Add tags')
                                    ->helperText('Press Enter to add each tag'),
                            ]),
                    ]),

                Section::make('Author Information')
                    ->description('Author details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('author_name')
                                    ->required()
                                    ->maxLength(255)
                                    ->default(fn () => Auth::user()?->name ?? 'Admin WOFINS')
                                    ->helperText('Author name (defaults to current user)'),
                                
                                Forms\Components\Select::make('author_title')
                                    ->required()
                                    ->options([
                                        'Financial Expert' => 'Financial Expert',
                                        'Wedding Consultant' => 'Wedding Consultant',
                                        'Business Analyst' => 'Business Analyst',
                                        'Content Manager' => 'Content Manager',
                                        'Technical Expert' => 'Technical Expert',
                                        'Marketing Expert' => 'Marketing Expert',
                                        'SEO Specialist' => 'SEO Specialist',
                                        'Admin WOFINS' => 'Admin WOFINS',
                                    ])
                                    ->default(function () {
                                        $user = Auth::user();
                                        if (!$user) return 'Financial Expert';
                                        
                                        // Smart default based on user email
                                        $email = strtolower($user->email);
                                        if (str_contains($email, 'admin') || str_contains($email, 'manager')) {
                                            return 'Admin WOFINS';
                                        } elseif (str_contains($email, 'tech') || str_contains($email, 'dev')) {
                                            return 'Technical Expert';
                                        } elseif (str_contains($email, 'marketing')) {
                                            return 'Marketing Expert';
                                        } else {
                                            return 'Financial Expert';
                                        }
                                    })
                                    ->searchable()
                                    ->helperText('Select appropriate author title'),
                            ]),
                    ]),

                Section::make('Publishing Settings')
                    ->description('Publication and visibility settings')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('read_time')
                                    ->required()
                                    ->numeric()
                                    ->default(5)
                                    ->suffix('minutes')
                                    ->minValue(1)
                                    ->maxValue(60),
                                
                                Forms\Components\Toggle::make('is_featured')
                                    ->helperText('Show in featured articles section'),
                                
                                Forms\Components\Toggle::make('is_published')
                                    ->helperText('Make article visible to public')
                                    ->default(true),
                            ]),
                        
                        Forms\Components\DateTimePicker::make('published_at')
                            ->default(now())
                            ->helperText('When should this article be published?'),
                    ]),

                Section::make('SEO Settings')
                    ->description('Search engine optimization')
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->maxLength(255)
                            ->helperText('SEO title (leave empty to use article title)'),
                        
                        Forms\Components\Textarea::make('meta_description')
                            ->maxLength(160)
                            ->helperText('SEO description (max 160 characters)')
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('views_count')
                            ->numeric()
                            ->default(0)
                            ->helperText('Article view count (for display purposes)'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')
                    ->label('Image')
                    ->square()
                    ->size(60),
                
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->wrap(),
                
                Tables\Columns\BadgeColumn::make('category')
                    ->searchable()
                    ->colors([
                        'primary' => 'Featured',
                        'success' => 'Tutorial',
                        'warning' => 'Business',
                        'info' => 'Tips',
                        'danger' => 'Keuangan',
                    ]),
                
                Tables\Columns\TextColumn::make('author_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('read_time')
                    ->label('Read Time')
                    ->suffix(' min')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->alignCenter(),
                
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('M j, Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'Featured' => 'Featured',
                        'Tutorial' => 'Tutorial',
                        'Business' => 'Business',
                        'Tips' => 'Tips',
                        'Keuangan' => 'Keuangan',
                    ]),
                
                Filter::make('is_featured')
                    ->label('Featured Articles')
                    ->query(fn (Builder $query): Builder => $query->where('is_featured', true)),
                
                Filter::make('is_published')
                    ->label('Published Articles')
                    ->query(fn (Builder $query): Builder => $query->where('is_published', true)),
                
                Filter::make('published_this_month')
                    ->label('Published This Month')
                    ->query(fn (Builder $query): Builder => $query->whereMonth('published_at', now()->month)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('toggle_featured')
                        ->label('Toggle Featured')
                        ->icon('heroicon-o-star')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_featured' => !$record->is_featured]);
                            }
                        }),
                    Tables\Actions\BulkAction::make('toggle_published')
                        ->label('Toggle Published')
                        ->icon('heroicon-o-eye')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_published' => !$record->is_published]);
                            }
                        }),
                ]),
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
        return [
            BlogResource\Widgets\BlogStatsWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogs::route('/'),
            'create' => Pages\CreateBlog::route('/create'),
            'edit' => Pages\EditBlog::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    
    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }
    
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery();
    }
    
    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'excerpt', 'author_name', 'category'];
    }
    
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Category' => $record->category,
            'Author' => $record->author_name,
            'Published' => $record->published_at?->format('M j, Y'),
        ];
    }
}
