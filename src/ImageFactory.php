<?php

declare(strict_types=1);

namespace NAttreid\ImageStorage;

use NAttreid\ImageStorage\Resources\ImageResource;
use NAttreid\Utils\Strings;
use Nette\Utils\Image;
use Nette\Utils\UnknownImageFileException;

/**
 * Class ImageFactory
 *
 * @author Attreid <attreid@gmail.com>
 */
class ImageFactory
{
	/** @var string */
	private $publicDir;

	/** @var string */
	private $path;

	/** @var string */
	private $flag;

	/** @var int */
	private $quality;

	/** @var string */
	private $relativePublicDir;

	/** @var null|string */
	private $noImage;

	public function __construct(string $path, string $publicDir, string $relativePublicDir, ?string $noImage, int $quality, string $flag)
	{
		$this->path = $path;
		$this->publicDir = $publicDir;
		$this->flag = @constant(Image::class . '::' . trim(Strings::upper($flag)));
		$this->quality = $quality;
		$this->relativePublicDir = $relativePublicDir;
		$this->noImage = $noImage;
	}

	private function getNoImage(ImageResource $origResource): ImageResource
	{
		$resource = new ImageResource($this->path . '/' . $this->noImage);
		$resource->cloneSettings($origResource);
		$resource->setNamespace($this->parseNamespace($this->noImage));

		return $resource;
	}

	public function parseNamespace(string $identifier): ?string
	{
		$pos = strrpos($identifier, '/');
		if ($pos !== false) {
			return substr($identifier, 0, $pos);
		}
		return null;
	}

	/**
	 * @param ImageResource $resource
	 * @return string
	 * @throws UnknownImageFileException
	 */
	public function create(ImageResource $resource): string
	{
		$source = $resource->file;
		if (!$resource->isOk() || !$resource->isImage()) {
			if ($this->noImage === null) {
				throw new UnknownImageFileException("File '$source' not found.");
			}
			return $this->create($this->getNoImage($resource));
		}
		$link = $resource->createLink();
		$path = $this->publicDir . '/' . $link;
		if (!file_exists($path)) {
			@mkdir(dirname($path), 0777, true);

			if ($resource->isSvg()) {
				@copy($source, $path);
			} else {
				$image = Image::fromFile($source);
				if ($resource->width || $resource->height) {
					$image->resize(
						$resource->width ?? $image->width,
						$resource->height ?? $image->height,
						$resource->flag ?? $this->flag
					);
				}

				$image->save($path, $resource->quality ?? $this->quality);
			}
		}
		return '/' . $this->relativePublicDir . '/' . $link;
	}
}