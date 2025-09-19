<?php

namespace Sindyko\ModularModify\Console\Commands;

use Sindyko\ModularModify\Support\ModuleConfig;
use Sindyko\ModularModify\Support\ModuleRegistry;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputOption;

trait Modularize
{
	protected function module(): ?ModuleConfig
	{
		if ($name = $this->option('module')) {
			$registry = $this->getLaravel()->make(ModuleRegistry::class);

			if ($module = $registry->module($name)) {
				return $module;
			}

			throw new InvalidOptionException(sprintf('The "%s" module does not exist.', $name));
		}

		return null;
	}

	protected function configure()
	{
		parent::configure();

		$this->getDefinition()->addOption(
			new InputOption(
				'--module',
				null,
				InputOption::VALUE_REQUIRED,
				'Run inside an application module'
			)
		);
	}
}
