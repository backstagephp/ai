<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Providers
    |--------------------------------------------------------------------------
    |
    | Configure your AI providers here. The key is the model name and the value
    | is the provider string that maps to Prism\Prism\Enums\Provider.
    |
    | Available providers: 'anthropic', 'deepseek', 'ollama', 'openai',
    | 'openrouter', 'mistral', 'groq', 'xai', 'gemini', 'voyageai', 'elevenlabs'
    |
    */
    'providers' => [
        'gpt-5.1' => 'openai',
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
