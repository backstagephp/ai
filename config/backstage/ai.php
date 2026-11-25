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

    /*
    |--------------------------------------------------------------------------
    | Generation defaults
    |--------------------------------------------------------------------------
    |
    | The defaults of the "Configuration" section in the AI modal. Both values
    | are sent to the model. An answer that does not fit within max_tokens is
    | not written to the field, so keep this roomy enough for the longest text
    | you expect. Temperature is left out for models that reject it.
    |
    */
    'configuration' => [
        'max_tokens' => 1000,
        'temperature' => 0.7,
    ],
];
