<?php

use Prism\Prism\Enums\Provider;

return [
    'providers' => [
        'gpt-4o-mini' => Provider::OpenAI,
    ],

    'action' => [
        'label' => 'AI',
        'icon' => 'heroicon-o-sparkles',
        'modal' => [
            'heading' => 'Generate with AI',
        ],
    ],

    'configuration' => [
        'max_tokens' => 100,
        'temperature' => 0.7,
    ],
];
