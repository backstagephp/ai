<?php

namespace Backstage\AI\Listeners;

use Backstage\AI\Events\CaptureAiRequest;
use Backstage\AI\Models\Prism\Response;

class RecordPrismResponse
{
    /**
     * Handle the event.
     */
    public function handle(CaptureAiRequest $event): void
    {
        $response = $event->response;

        /**
         * @var \Backstage\Ai\Models\Prism\Response $prismResponse
         */
        $prismResponse = Response::create([
            'text' => $response->text,
            'finish_reason' => $response->finishReason?->name,
            'prompt_tokens' => $response->usage?->promptTokens,
            'completion_tokens' => $response->usage?->completionTokens,
            'cache_write_input_tokens' => $response->usage?->cacheWriteInputTokens,
            'cache_read_input_tokens' => $response->usage?->cacheReadInputTokens,
            'response_id' => $response->responseMeta->id,
            'model' => $response->responseMeta->model,
        ]);

        foreach ($response->responseMeta->rateLimits ?? [] as $rateLimit) {
            $prismResponse->rateLimits()->create(['data' => $rateLimit]);
        }

        foreach ($response->toolCalls ?? [] as $toolCall) {
            $prismResponse->toolCalls()->create(['data' => $toolCall]);
        }

        foreach ($response->toolResults ?? [] as $toolResult) {
            $prismResponse->toolResults()->create(['data' => $toolResult]);
        }

        foreach ($response->responseMessages ?? [] as $message) {
            $prismResponse->responseMessages()->create([
                'role' => 'assistant',
                'content' => $message->content,
                'tool_calls' => $message->toolCalls,
                'additional_content' => $message->additionalContent,
            ]);
        }

        foreach ($response->steps ?? [] as $step) {
            $stepModel = $prismResponse->steps()->create([
                'text' => $step->text,
                'finish_reason' => $step->finishReason?->name,
                'prompt_tokens' => $step->usage?->promptTokens,
                'completion_tokens' => $step->usage?->completionTokens,
                'cache_write_input_tokens' => $step->usage?->cacheWriteInputTokens,
                'cache_read_input_tokens' => $step->usage?->cacheReadInputTokens,
                'response_id' => $step->responseMeta->id,
                'model' => $step->responseMeta->model,
            ]);

            foreach ($step->toolCalls ?? [] as $toolCall) {
                $stepModel->toolCalls()->create(['data' => $toolCall]);
            }

            foreach ($step->toolResults ?? [] as $toolResult) {
                $stepModel->toolResults()->create(['data' => $toolResult]);
            }

            foreach ($step->messages ?? [] as $msg) {
                if ($msg instanceof \EchoLabs\Prism\ValueObjects\Messages\UserMessage) {
                    $role = 'user';
                    $content = $msg->text();
                } elseif ($msg instanceof \EchoLabs\Prism\ValueObjects\Messages\AssistantMessage) {
                    $role = 'assistant';
                    $content = $msg->content;
                }

                $stepModel->messages()->create([
                    'role' => $role,
                    'content' => $content,
                    'tool_calls' => $msg->toolCalls ?? [],
                    'additional_content' => $msg->additionalContent ?? [],
                ]);
            }
        }
    }
}
