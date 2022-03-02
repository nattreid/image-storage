<?php

declare(strict_types=1);

namespace NAttreid\ImageStorage\DI;

use NAttreid\ImageStorage\ImageFactory;
use NAttreid\ImageStorage\ImageStorage;
use NAttreid\ImageStorage\Macros\Macro;
use Nette\DI\CompilerExtension;
use Nette\DI\Helpers;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

class ImageStorageExtension extends CompilerExtension
{
	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'assetsPath' => Expect::string()->default('%wwwDir%/../assets'),
			'wwwDir' => Expect::string()->default('%wwwDir%'),
			'publicDir' => Expect::string()->default('assets'),
			'relativePath' => Expect::string()->default(null),
			'quality' => Expect::int()->default(85),
			'defaultFlag' => Expect::string()->default('fit'),
			'noImage' => Expect::string()->default(null),
			'domain' => Expect::string()->default(null),
			'timeout' => Expect::int()->default(10),
		]);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		$this->config->assetsPath = Helpers::expand($this->config->assetsPath, $builder->parameters);
		$this->config->wwwDir = Helpers::expand($this->config->wwwDir, $builder->parameters);
		$this->config->relativePath = $this->config->relativePath ?? $this->config->publicDir;

		$builder->addDefinition($this->prefix('storage'))
			->setType(ImageStorage::class)
			->setArguments([
				$this->config->assetsPath,
				$this->config->wwwDir . '/' . $this->config->publicDir,
				$this->config->domain,
				$this->config->timeout
			]);

		$builder->addDefinition($this->prefix('factory'))
			->setType(ImageFactory::class)
			->setArguments([
				$this->config->assetsPath,
				$this->config->wwwDir . '/' . $this->config->publicDir,
				$this->config->relativePatch,
				$this->config->noImage,
				$this->config->quality,
				$this->config->defaultFlag
			]);
	}

	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();
		$builder->getDefinition('nette.latteFactory')
			->getResultDefinition()
			->addSetup(Macro::class . '::install(?->getCompiler())', array('@self'));
	}
}