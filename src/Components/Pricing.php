<?php

namespace Backstage\AI\Components;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class Pricing extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public function render()
    {
        $icon = 'heroicon-o-currency-dollar';

        return view('ai::components.pricing', compact('icon'));
    }

    public function renderAction(): Action
    {
        return Action::make('renderAction')
            ->hiddenLabel()
            ->icon('heroicon-m-currency-dollar')
            ->modalWidth(MaxWidth::TwoExtraLarge)
            ->modal()
            ->modalHeading('')
            ->modalContent(fn () => new HtmlString(Blade::render("@livewire('ai::pricing-info')")))
            ->color('secondary');
    }

    public function mountRenderAction()
    {
        $this->mountAction('renderAction');
    }
}
