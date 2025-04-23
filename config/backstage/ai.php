<?php

use EchoLabs\Prism\Enums\Provider;


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

    'pricing' => [
        'gpt-4o-mini' => [
            'price_per_1m_tokens' => 0.15,
            'currency' => 'USD',
        ],

        'revenue_percentage_factor' => 1.2,

        'models' => [
            'gpt-4.1' => [
                'version' => '2025-04-14',
                'input' => 2.00,
                'cached_input' => 0.50,
                'output' => 8.00,
            ],
            'gpt-4.1-mini' => [
                'version' => '2025-04-14',
                'input' => 0.40,
                'cached_input' => 0.10,
                'output' => 1.60,
            ],
            'gpt-4.1-nano' => [
                'version' => '2025-04-14',
                'input' => 0.10,
                'cached_input' => 0.025,
                'output' => 0.40,
            ],
            'gpt-4.5-preview' => [
                'version' => '2025-02-27',
                'input' => 75.00,
                'cached_input' => 37.50,
                'output' => 150.00,
            ],
            'gpt-4o' => [
                'version' => '2024-08-06',
                'input' => 2.50,
                'cached_input' => 1.25,
                'output' => 10.00,
            ],
            'gpt-4o-audio-preview' => [
                'version' => '2024-12-17',
                'input' => 2.50,
                'cached_input' => null,
                'output' => 10.00,
            ],
            'gpt-4o-realtime-preview' => [
                'version' => '2024-12-17',
                'input' => 5.00,
                'cached_input' => 2.50,
                'output' => 20.00,
            ],
            'gpt-4o-mini' => [
                'version' => '2024-07-18',
                'input' => 0.15,
                'cached_input' => 0.075,
                'output' => 0.60,
            ],
            'gpt-4o-mini-audio-preview' => [
                'version' => '2024-12-17',
                'input' => 0.15,
                'cached_input' => null,
                'output' => 0.60,
            ],
            'gpt-4o-mini-realtime-preview' => [
                'version' => '2024-12-17',
                'input' => 0.60,
                'cached_input' => 0.30,
                'output' => 2.40,
            ],
            'o1' => [
                'version' => '2024-12-17',
                'input' => 15.00,
                'cached_input' => 7.50,
                'output' => 60.00,
            ],
            'o1-pro' => [
                'version' => '2025-03-19',
                'input' => 150.00,
                'cached_input' => null,
                'output' => 600.00,
            ],
            'o3' => [
                'version' => '2025-04-16',
                'input' => 10.00,
                'cached_input' => 2.50,
                'output' => 40.00,
            ],
            'o4-mini' => [
                'version' => '2025-04-16',
                'input' => 1.10,
                'cached_input' => 0.275,
                'output' => 4.40,
            ],
            'o3-mini' => [
                'version' => '2025-01-31',
                'input' => 1.10,
                'cached_input' => 0.55,
                'output' => 4.40,
            ],
        ],
    ],
];
