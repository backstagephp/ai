<?php

namespace Backstage\AI\Resources;

use Backstage\AI\Models\Prompt;
use Backstage\AI\Resources\PromptResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Prism\Prism\Enums\Provider;

class PromptResource extends Resource
{
    protected static ?string $model = Prompt::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Split::make([
                    Forms\Components\Section::make(__('AI Prompt'))
                        ->description(__('Manage your AI prompt'))
                        ->icon('heroicon-s-rectangle-stack')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->helperText(__('The name of the prompt, used to identify it in the system'))
                                ->unique(ignoreRecord: true)
                                ->prefixIcon(static::getNavigationIcon())
                                ->label(__('Name')),

                            Forms\Components\Textarea::make('prompt')
                                ->required()
                                ->autosize()
                                ->rows(10)
                                ->hint(static::getPromptHint())
                                ->helperText(__('The prompt that will be used to generate the text in actions where this prompt is used'))
                                ->label(__('Prompt')),
                        ])
                        ->grow(true),

                    Forms\Components\Section::make(__('Advanced Options'))
                        ->description(__('Advanced configuration for the AI prompt'))
                        ->icon('heroicon-s-adjustments-horizontal')
                        ->schema([
                            Forms\Components\Select::make('model')
                                ->label(__('AI model'))
                                ->searchable()
                                ->required()
                                ->preload()
                                ->columnSpanFull()
                                ->native(false)
                                ->placeholder(__('Select an AI model'))
                                ->options(fn () => collect(config('backstage.ai.providers'))->mapWithKeys(fn ($item, $key) => [ucfirst($key) => $item])),

                            Forms\Components\TextInput::make('temperature')
                                ->numeric()
                                ->label('Temperature')
                                ->default(config('backstage.ai.configuration.temperature'))
                                ->helperText('The higher the temperature, the more creative the text')
                                ->maxValue(1)
                                ->minValue(0)
                                ->required()
                                ->step('0.1'),

                            Forms\Components\TextInput::make('max_tokens')
                                ->numeric()
                                ->label('Max tokens')
                                ->default(config('backstage.ai.configuration.max_tokens'))
                                ->helperText('The maximum number of tokens to generate')
                                ->step('10')
                                ->required()
                                ->minValue(0)
                                ->default(100)
                                ->suffixAction(
                                    Forms\Components\Actions\Action::make('increase')
                                        ->icon('heroicon-o-plus')
                                        ->action(fn (Set $set, Get $get) => $set('max_tokens', $get('max_tokens') + 100)),
                                ),
                        ])
                        ->grow(false),
                ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description(__('Manage your (system) AI prompts'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('model')
                    ->label(__('AI model'))
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        $providers = config('backstage.ai.available-models');

                        $providerEnum = $providers[$state] ?? null;

                        if (! $providerEnum) {
                            return $state;
                        }

                        /**
                         * @var Provider|null $origin
                         */
                        $origin = $providerEnum['origin'] ?? null;

                        if (! $origin) {
                            return $state;
                        }

                        $originName = $origin->name;

                        return str($state)
                            ->append(' (')
                            ->append($originName)
                            ->append(')')
                            ->toString();
                    }),

            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->color('gray')
                    ->hiddenLabel()
                    ->tooltip(fn (Tables\Actions\EditAction $action) => $action->getLabel())
                    ->button(),

                Tables\Actions\DeleteAction::make()
                    ->hiddenLabel()
                    ->tooltip(fn (Tables\Actions\DeleteAction $action) => $action->getLabel())
                    ->button(),

                Tables\Actions\ForceDeleteAction::make()
                    ->hiddenLabel()
                    ->tooltip(fn (Tables\Actions\ForceDeleteAction $action) => $action->getLabel())
                    ->button(),

                Tables\Actions\RestoreAction::make()
                    ->hiddenLabel()
                    ->tooltip(fn (Tables\Actions\RestoreAction $action) => $action->getLabel())
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\ForceDeleteBulkAction::make(),

                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrompts::route('/'),
            'create' => Pages\CreatePrompt::route('/create'),
            'edit' => Pages\EditPrompt::route('/{record}/edit'),
        ];
    }

    public static function getPromptHint(): HtmlString
    {
        return new HtmlString(
            str('')
                ->append('<span>')
                ->append(__('Learn how to write effective prompts'))
                ->append(' ')
                ->append('<a href="https://www.atlassian.com/blog/artificial-intelligence/ultimate-guide-writing-ai-prompts" target="_blank" class="text-primary-600 hover:text-primary-700 dark:text-primary-500 dark:hover:text-primary-400">')
                ->append(__('here'))
                ->append('</a>')
                ->append('</span>')
                ->toString()
        );
    }
}
