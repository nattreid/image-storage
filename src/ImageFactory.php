<?php

declare(strict_types=1);

namespace NAttreid\ImageStorage;

use NAttreid\ImageStorage\Resources\Resource;
use NAttreid\Utils\Strings;
use Nette\Utils\Image;

/**
 * Class ImageFactory
 *
 * @author Attreid <attreid@gmail.com>
 */
class ImageFactory
{
	/** @var string */
	private $dir;

	/** @var string */
	private $path;

	/** @var string */
	private $flag;

	/** @var int */
	private $quality;

	public function __construct(string $path, string $dir, int $quality, string $flag)
	{
		$this->dir = $dir;
		$this->path = $path;
		$this->flag = Image::${Strings::upper($flag)};
		$this->quality = $quality;
	}

	public function create(Resource $resource)
	{
		$link = $resource->createLink();
		if (!file_exists($this->dir . '/' . $link)) {
			$image = new Image($this->path . $resource->getIdentifier());
			if ($resource->width || $resource->height) {
				$image->resize(
					$resource->width ?? $image->width,
					$resource->height ?? $image->height,
					$resource->flag ?? $this->flag
				);
			}
			$image->save($link, $resource->quality ?? $this->quality);
		}
		return $link;
	}


}