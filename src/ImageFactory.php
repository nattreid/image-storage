<?php

declare(strict_types=1);

namespace NAttreid\ImageStorage;

use NAttreid\ImageStorage\Resources\ImageResource;
use NAttreid\Utils\Strings;
use Nette\Utils\Image;
use Nette\Utils\ImageException;
use Nette\Utils\UnknownImageFileException;

final class ImageFactory
{
	private string $publicDir;
	private string $path;
	private int $flag;
	private int $quality;
	private string $relativePublicDir;
	private ?string $noImage;

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
	 * @throws UnknownImageFileException|ImageException
	 */
	public function create(ImageResource $resource, string $domain = null): string
	{
		$source = $resource->file;
		if ($domain === null && (!$resource->isOk() || !$resource->isImage())) {
			if ($this->noImage === null) {
				throw new UnknownImageFileException("File '$source' not found.");
			}
			return $this->create($this->getNoImage($resource));
		}
		$link = $resource->createLink();
		$path = $this->publicDir . '/' . $link;
		if ($domain === null && !file_exists($path)) {
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
		return ($domain ?? '') . '/' . $this->relativePublicDir . '/' . $link;
	}
}