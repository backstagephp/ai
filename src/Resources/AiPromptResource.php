<?php

namespace Backstage\AI\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Backstage\AI\Models\AiPrompt;
use Illuminate\Database\Eloquent\Builder;
use Backstage\AI\Resources\AiPromptResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AiPromptResource extends Resource
{
    protected static ?string $model = AiPrompt::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('AI Prompt'))
                    ->description(__('Manage your AI prompt'))
                    ->icon('heroicon-s-rectangle-stack')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->helperText(__('The name of the prompt, used to identify it in the system'))
                            ->prefixIcon(static::getNavigationIcon())
                            ->label(__('Name')),

                        Forms\Components\Textarea::make('prompt')
                            ->required()
                            ->autosize()
                            ->helperText(__('The prompt that will be used to generate the text in actions where this prompt is used'))
                            ->label(__('Prompt'))
                    ]),
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
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->color('gray')
                    ->hiddenLabel()
                    ->tooltip(fn(Tables\Actions\EditAction $action) => $action->getLabel())
                    ->button(),

                Tables\Actions\DeleteAction::make()
                    ->hiddenLabel()
                    ->tooltip(fn(Tables\Actions\DeleteAction $action) => $action->getLabel())
                    ->button(),

                Tables\Actions\ForceDeleteAction::make()
                    ->hiddenLabel()
                    ->tooltip(fn(Tables\Actions\ForceDeleteAction $action) => $action->getLabel())
                    ->button(),

                Tables\Actions\RestoreAction::make()
                    ->hiddenLabel()
                    ->tooltip(fn(Tables\Actions\RestoreAction $action) => $action->getLabel())
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\ForceDeleteBulkAction::make(),

                    Tables\Actions\RestoreBulkAction::make(),
                ])
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes([
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
            'index' => Pages\ListAiPrompts::route('/'),
            'create' => Pages\CreateAiPrompt::route('/create'),
            'edit' => Pages\EditAiPrompt::route('/{record}/edit'),
        ];
    }
}
