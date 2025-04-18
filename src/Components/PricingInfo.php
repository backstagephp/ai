<?php

namespace Backstage\AI\Components;

use Backstage\AI\Models\Prism\Response;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Sushi\Sushi;

class PricingInfo extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function render()
    {
        $icon = config('backstage.ai.icon', 'heroicon-o-currency-dollar');

        return view('ai::components.pricing-info', compact('icon'));
    }

    protected function getTableQuery(): Builder | Relation | null
    {
        $customSushi = new class extends Model
        {
            use Sushi;

            public function getTable()
            {
                return Filament::auth()->user()->getTable(); // !! NOT IMPORTANT
            }

            public function getRows()
            {
                $models = config('backstage.ai.providers');

                $pricing = collect($models)->map(function ($model, $key) {
                    $priceConfig = config('backstage.ai.pricing.' . $key);
                    if ($priceConfig === null) {
                        return null;
                    }

                    $usage = Response::query()
                        ->where('model', 'LIKE', $key . '%')
                        ->where('created_at', '>=', now()->startOfMonth())
                        ->where('created_at', '<=', now()->endOfMonth())
                        ->sum('prompt_tokens');

                    $usage += Response::query()
                        ->where('model', 'LIKE', $key . '%')
                        ->where('created_at', '>=', now()->startOfMonth())
                        ->where('created_at', '<=', now()->endOfMonth())
                        ->sum('completion_tokens');

                    $configuration = [];

                    $configuration['model'] = $key;
                    $configuration['price_per_1m_tokens'] = $priceConfig['price_per_1m_tokens'] ?? 0;
                    $configuration['currency'] = $priceConfig['currency'] ?? 'USD';
                    $configuration['usage'] = $usage ?? 0;
                    $configuration['price'] = PricingInfo::getPrice($usage, $key);

                    return $configuration;
                })
                    ->values();

                return $pricing->toArray();
            }
        };

        return $customSushi->query();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->heading(new HtmlString(
                __('Total pricing for this month: :amount', [
                    'amount' => ($price = $this->getTableQuery()->get()->sum('price')),
                ])
            ))
            ->description(__('Total usage for this month: :amount', [
                'amount' => $this->getTableQuery()->get()->sum('usage') . ' tokens',
            ]))
            ->columns([
                TextColumn::make('model')
                    ->label(__('Model')),

                TextColumn::make('price')
                    ->label(__('Price'))
                    ->money('USD')
                    ->numeric(str(strrchr($price, '.'))->replace([',', '.'], '')->length()),

                TextColumn::make('usage')
                    ->label(__('Usage'))
                    ->formatStateUsing(fn ($state) => $state . ' tokens'),
            ])
            ->paginated(false);
    }

    public static function getPrice(int $usage, string $key)
    {
        $pricingConfig = config('backstage.ai.pricing.' . $key);

        $pricePer1mTokens = $pricingConfig['price_per_1m_tokens'] ?? 0;
        $revenuePercentageFactor = $pricingConfig['revenue_percentage_factor'] ?? 1.2;

        $price = ($usage / 1_000_000) * $pricePer1mTokens;

        $priceWithRevenue = $price * $revenuePercentageFactor;

        return $priceWithRevenue;
    }
}
