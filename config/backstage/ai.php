<?php

use Prism\Prism\Enums\Provider;

return [
    'providers' => [
        Provider::OpenAI->value => [
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
            'gpt-4' => 'GPT-4',
            'gpt-4-32k' => 'GPT-4 32k',
            'gpt-4-turbo' => 'GPT-4 Turbo',
            'gpt-4-turbo-32k' => 'GPT-4 Turbo 32k',
        ]
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
