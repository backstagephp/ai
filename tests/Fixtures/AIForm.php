<?php

namespace Backstage\AI\Tests\Fixtures;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Component;

class AIForm extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('description')->withAI(),

                RichEditor::make('body')->withAI(),

                RichEditor::make('document')->json()->withAI(),

                MarkdownEditor::make('markdown')->withAI(),

                TextInput::make('title')
                    ->maxLength(60)
                    ->withAI(fn (Get $get) => 'Write a title for: ' . $get('description')),

                Textarea::make('note')->withAI(context: 'An internal note about a customer.'),

                Textarea::make('dynamic')->withAI(context: fn (Get $get) => 'A summary of ' . $get('title')),

                Textarea::make('summary')->withAI('Summarise the text'),

                Textarea::make('copied')->withAI('Copy'),
            ])
            ->statePath('data');
    }

    public function render(): string
    {
        return <<<'BLADE'
            <div>
                {{ $this->form }}

                <x-filament-actions::modals />
            </div>
        BLADE;
    }
}
