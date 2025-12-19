<?php

namespace Tonso\TrelloTracker;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Stevenmaguire\Services\Trello\Client;
use Tonso\TrelloTracker\AI\AiIntentAnalyzer;
use Tonso\TrelloTracker\AI\Clients\OpenAILLMClient;
use Tonso\TrelloTracker\AI\Contracts\LLMClient;
use Tonso\TrelloTracker\Console\Commands\MonitorIdleTranscripts;
use Tonso\TrelloTracker\Messaging\Adapters\WhatsAppAdapter;
use Tonso\TrelloTracker\Services\Trello\TrelloOrchestrator;
use Tonso\TrelloTracker\Services\Trello\TrelloService;
use Tonso\TrelloTracker\Services\WhatsappService;
use Tonso\TrelloTracker\UseCases\ProcessIncomingMessage;

class TrelloTrackerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'trello-tracker-migrations');

            $this->commands([
                MonitorIdleTranscripts::class,
            ]);

            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $schedule->command('trello-tracker:monitor-idle')->everyFiveMinutes();
            });
        }

        $this->mergeConfigFrom(
            __DIR__ . '/../config/trello-tracker.php',
            'trello-tracker'
        );

        $this->app->singleton(Client::class, function () {
            return new Client([
                'key'   => config('trello-tracker.task_managers.trello.key'),
                'token' => config('trello-tracker.task_managers.trello.token'),
            ]);
        });

        $this->app->singleton(LLMClient::class, function () {
            return new OpenAILLMClient(
                apiKey: config('trello-tracker.ai.openai.key'),
                model: config('trello-tracker.ai.openai.model', 'gpt-4.1-mini'),
            );
        });

        $this->app->singleton(TrelloService::class, function ($app) {
            return new TrelloService(
                client: $app->make(Client::class),
                boardId: config('trello-tracker.task_managers.trello.board_id'),
                defaultListId: config('trello-tracker.task_managers.trello.default_list_id'),
            );
        });

        $this->app->singleton(TrelloOrchestrator::class);
        $this->app->singleton(WhatsappService::class);

        $this->app->singleton(AiIntentAnalyzer::class);
        $this->app->singleton(ProcessIncomingMessage::class);

        $this->app->singleton(WhatsAppAdapter::class);
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        $this->publishes([
            __DIR__ . '/../config/trello-tracker.php' => config_path('trello-tracker.php'),
        ], 'trello-tracker-config');
    }
}