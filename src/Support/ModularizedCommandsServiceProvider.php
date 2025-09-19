<?php

namespace Sindyko\ModularModify\Support;

use Illuminate\Console\Application;
use Illuminate\Console\Application as Artisan;
use Illuminate\Database\Console\Migrations\MigrateMakeCommand as OriginalMakeMigrationCommand;
use Illuminate\Support\ServiceProvider;
use Sindyko\ModularModify\Console\Commands\Database\SeedCommand;
use Sindyko\ModularModify\Console\Commands\Make\MakeCast;
use Sindyko\ModularModify\Console\Commands\Make\MakeChannel;
use Sindyko\ModularModify\Console\Commands\Make\MakeCommand;
use Sindyko\ModularModify\Console\Commands\Make\MakeComponent;
use Sindyko\ModularModify\Console\Commands\Make\MakeController;
use Sindyko\ModularModify\Console\Commands\Make\MakeEvent;
use Sindyko\ModularModify\Console\Commands\Make\MakeException;
use Sindyko\ModularModify\Console\Commands\Make\MakeFactory;
use Sindyko\ModularModify\Console\Commands\Make\MakeJob;
use Sindyko\ModularModify\Console\Commands\Make\MakeListener;
use Sindyko\ModularModify\Console\Commands\Make\MakeLivewire;
use Sindyko\ModularModify\Console\Commands\Make\MakeMail;
use Sindyko\ModularModify\Console\Commands\Make\MakeMiddleware;
use Sindyko\ModularModify\Console\Commands\Make\MakeMigration;
use Sindyko\ModularModify\Console\Commands\Make\MakeModel;
use Sindyko\ModularModify\Console\Commands\Make\MakeNotification;
use Sindyko\ModularModify\Console\Commands\Make\MakeObserver;
use Sindyko\ModularModify\Console\Commands\Make\MakePolicy;
use Sindyko\ModularModify\Console\Commands\Make\MakeProvider;
use Sindyko\ModularModify\Console\Commands\Make\MakeRequest;
use Sindyko\ModularModify\Console\Commands\Make\MakeResource;
use Sindyko\ModularModify\Console\Commands\Make\MakeRule;
use Sindyko\ModularModify\Console\Commands\Make\MakeSeeder;
use Sindyko\ModularModify\Console\Commands\Make\MakeTest;
use Livewire\Commands as Livewire;

class ModularizedCommandsServiceProvider extends ServiceProvider
{
	protected array $overrides = [
		'command.cast.make' => MakeCast::class,
		'command.controller.make' => MakeController::class,
		'command.console.make' => MakeCommand::class,
		'command.channel.make' => MakeChannel::class,
		'command.event.make' => MakeEvent::class,
		'command.exception.make' => MakeException::class,
		'command.factory.make' => MakeFactory::class,
		'command.job.make' => MakeJob::class,
		'command.listener.make' => MakeListener::class,
		'command.mail.make' => MakeMail::class,
		'command.middleware.make' => MakeMiddleware::class,
		'command.model.make' => MakeModel::class,
		'command.notification.make' => MakeNotification::class,
		'command.observer.make' => MakeObserver::class,
		'command.policy.make' => MakePolicy::class,
		'command.provider.make' => MakeProvider::class,
		'command.request.make' => MakeRequest::class,
		'command.resource.make' => MakeResource::class,
		'command.rule.make' => MakeRule::class,
		'command.seeder.make' => MakeSeeder::class,
		'command.test.make' => MakeTest::class,
		'command.component.make' => MakeComponent::class,
		'command.seed' => SeedCommand::class,
	];

	public function register(): void
	{
		// Register our overrides via the "booted" event to ensure that we override
		// the default behavior regardless of which service provider happens to be
		// bootstrapped first (this mostly matters for Livewire).
		$this->app->booted(function() {
			Artisan::starting(function(Application $artisan) {
				$this->registerMakeCommandOverrides();
				$this->registerMigrationCommandOverrides();
				$this->registerLivewireOverrides($artisan);
			});
		});
	}

	protected function registerMakeCommandOverrides()
	{
		foreach ($this->overrides as $alias => $class_name) {
			$this->app->singleton($alias, $class_name);
			$this->app->singleton(get_parent_class($class_name), $class_name);
		}
	}

	protected function registerMigrationCommandOverrides()
	{
		// Laravel 8
		$this->app->singleton('command.migrate.make', function($app) {
			return new MakeMigration($app['migration.creator'], $app['composer']);
		});

		// Laravel 9
		$this->app->singleton(OriginalMakeMigrationCommand::class, function($app) {
			return new MakeMigration($app['migration.creator'], $app['composer']);
		});
	}

	protected function registerLivewireOverrides(Artisan $artisan)
	{
		// Don't register commands if Livewire isn't installed
		if (! class_exists(Livewire\MakeCommand::class)) {
			return;
		}

		// Replace the resolved command with our subclass
		$artisan->resolveCommands([MakeLivewire::class]);

		// Ensure that if 'make:livewire' or 'livewire:make' is resolved from the container
		// in the future, our subclass is used instead
		$this->app->extend(Livewire\MakeCommand::class, function() {
			return new MakeLivewire();
		});
		$this->app->extend(Livewire\MakeLivewireCommand::class, function() {
			return new MakeLivewire();
		});
	}
}
