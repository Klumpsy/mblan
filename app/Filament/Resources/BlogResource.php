<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogResource\Pages\CreateBlog;
use App\Filament\Resources\BlogResource\Pages\EditBlog;
use App\Filament\Resources\BlogResource\Pages\ListBlogs;
use App\Models\Blog;
use App\Models\Tag;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BlogResource extends Resource
{
    protected static ?string $model = Blog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Blog Content')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(string $context, $state, callable $set) => $context === 'create' ? $set('slug', Str::slug($state)) : null),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Blog::class, 'slug', ignoreRecord: true)
                            ->rules(['alpha_dash'])
                            ->helperText('URL-friendly version of the title'),

                        Select::make('author_id')
                            ->label('Author')
                            ->options(User::all()->pluck('name', 'id'))
                            ->required()
                            ->default(Auth::id())
                            ->searchable(),

                        Select::make('tags')
                            ->label('Tags')
                            ->multiple()
                            ->relationship('tags', 'name')
                            ->options(
                                Tag::where(function ($query) {
                                    $query->where('model_type', Blog::class)
                                        ->orWhereNull('model_type');
                                })->pluck('name', 'id')
                            )
                            ->searchable(),

                        Textarea::make('preview_text')
                            ->label('Preview Text')
                            ->maxLength(500)
                            ->rows(3)
                            ->helperText('Short description shown in blog listings'),
                    ])
                    ->columns(2),

                Section::make('Blog Body')
                    ->schema([
                        RichEditor::make('content')
                            ->required()
                            ->toolbarButtons([
                                'attachFiles',
                                'blockquote',
                                'bold',
                                'bulletList',
                                'codeBlock',
                                'h2',
                                'h3',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'underline',
                                'undo',
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('Media & Publishing')
                    ->schema([
                        FileUpload::make('image')
                            ->label('Featured Image')
                            ->image()
                            ->directory('blog-images')
                            ->disk('public')
                            ->visibility('public')
                            ->required()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->maxSize(2048)
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ]),

                        Toggle::make('published')
                            ->label('Published')
                            ->default(false)
                            ->helperText('Toggle to publish/unpublish this blog post'),

                        DateTimePicker::make('published_at')
                            ->label('Publish Date')
                            ->default(now())
                            ->required()
                            ->helperText('When this blog post should be published'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Image')
                    ->square()
                    ->size(50),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

                TextColumn::make('author.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('published')
                    ->boolean()
                    ->sortable(),
                ToggleColumn::make('is_featured')
                    ->label('Featured')
                    ->afterStateUpdated(function ($record, $state) {
                        if ($state) {
                            Blog::where('id', '!=', $record->id)
                                ->where('is_featured', true)
                                ->update(['is_featured' => false]);
                        }
                    }),

                TextColumn::make('published_at')
                    ->label('Publish Date')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),

                TextColumn::make('comments_count')
                    ->label('Comments')
                    ->counts('comments')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('published')
                    ->label('Publication Status')
                    ->boolean()
                    ->trueLabel('Published')
                    ->falseLabel('Draft')
                    ->native(false),

                SelectFilter::make('author_id')
                    ->label('Author')
                    ->options(User::all()->pluck('name', 'id'))
                    ->searchable(),

                Filter::make('published_at')
                    ->form([
                        DatePicker::make('published_from')
                            ->label('Published from'),
                        DatePicker::make('published_until')
                            ->label('Published until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['published_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('published_at', '>=', $date),
                            )
                            ->when(
                                $data['published_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('published_at', '<=', $date),
                            );
                    }),
            ])

            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('publish')
                        ->label('Publish Selected')
                        ->icon('heroicon-o-eye')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'published' => true,
                                    'published_at' => $record->published_at ?? now(),
                                ]);
                            });
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('unpublish')
                        ->label('Unpublish Selected')
                        ->icon('heroicon-o-eye-slash')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['published' => false]);
                            });
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBlogs::route('/'),
            'create' => CreateBlog::route('/create'),
            'edit' => EditBlog::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('published', false)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::where('published', false)->count() > 0 ? 'warning' : null;
    }
}
