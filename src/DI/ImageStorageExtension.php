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
		'path' => '%wwwDir%/../public/data',
		'wwwDir' => '%wwwDir%',
		'dir' => 'data',
		'quality' => 85,
		'defaultFlag' => 'fit',
		'noimage_identifier' => 'noimage/03/no-image.png'
	];

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults, $this->getConfig());

		$config['path'] = Helpers::expand($config['path'], $builder->parameters);
		$config['wwwDir'] = Helpers::expand($config['wwwDir'], $builder->parameters);

		$builder->addDefinition($this->prefix('storage'))
			->setClass(ImageStorage::class)
			->setArguments([
				$config['path']
			]);

		$builder->addDefinition($this->prefix('factory'))
			->setClass(ImageFactory::class)
			->setArguments([
				$config['path'],
				$config['wwwDir'] . '/' . $config['dir'],
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