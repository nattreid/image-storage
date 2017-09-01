<?php

declare(strict_types=1);

namespace NAttreid\ImageStorage\DI;

use NAttreid\ImageStorage\ImageFactory;
use NAttreid\ImageStorage\ImageStorage;
use NAttreid\ImageStorage\Macros\Macros;
use Nette\DI\CompilerExtension;
use Nette\DI\Helpers;

/**
 * Class ImageStorageExtension
 *
 * @author Attreid <attreid@gmail.com>
 */
class ImageStorageExtension extends CompilerExtension
{
	private $defaults = [
		'assetsPath' => '%wwwDir%/../assets',
		'wwwDir' => '%wwwDir%',
		'publicDir' => 'assets',
		'quality' => 85,
		'defaultFlag' => 'fit',
		'noImage' => null,
		'timeout' => 10
	];

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults, $this->getConfig());

		$config['assetsPath'] = Helpers::expand($config['assetsPath'], $builder->parameters);
		$config['wwwDir'] = Helpers::expand($config['wwwDir'], $builder->parameters);

		$builder->addDefinition($this->prefix('storage'))
			->setClass(ImageStorage::class)
			->setArguments([
				$config['assetsPath'],
				$config['wwwDir'] . '/' . $config['publicDir'],
				$config['timeout']
			]);

		$builder->addDefinition($this->prefix('factory'))
			->setClass(ImageFactory::class)
			->setArguments([
				$config['assetsPath'],
				$config['wwwDir'] . '/' . $config['publicDir'],
				$config['publicDir'],
				$config['noImage'],
				$config['quality'],
				$config['defaultFlag']
			]);
	}

	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();
		$builder->getDefinition('nette.latteFactory')
			->addSetup(Macros::class . '::install(?->getCompiler())', array('@self'));
	}
}