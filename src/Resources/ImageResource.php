<?php

declare(strict_types=1);

namespace NAttreid\ImageStorage\Resources;

use Nette\Utils\Image;
use Nette\Utils\Strings;

/**
 * @property-read int|null $width
 * @property-read int|null $height
 * @property-read int|null $flag
 * @property-read int|null $quality
 */
final class ImageResource extends FileResource
{
	private ?int $width = null;
	private ?int $height = null;
	private ?int $flag = null;
	private ?int $quality = null;

	public function setQuality(?int $quality): void
	{
		$this->quality = $quality;
	}

	public function setSize(?string $size): void
	{
		if ($size !== null) {
			@list($width, $height) = explode('x', $size);
			$this->width = intval($width) ?: null;
			$this->height = intval($height) ?: null;
		}
	}

	public function setFlag(?string $flag): void
	{
		if ($flag === null) {
			$this->flag === null;
		} else {
			$this->flag = @constant(Image::class . '::' . trim(Strings::upper($flag)));
		}
	}

	protected function getQuality(): ?int
	{
		return $this->quality;
	}

	protected function getWidth(): ?int
	{
		return $this->width;
	}

	protected function getHeight(): ?int
	{
		return $this->height;
	}

	protected function getFlag(): ?int
	{
		return $this->flag;
	}

	public function cloneSettings($origResource): void
	{
		$this->flag = $origResource->flag;
		$this->width = $origResource->width;
		$this->height = $origResource->height;
		$this->quality = $origResource->quality;
	}

	public function createLink(): string
	{
		$link = $this->namespace;
		if ($link) {
			$link .= '/';
		}

		$dirName = $this->createDirName();
		if ($dirName) {
			$link .= $dirName . '/';
		}

		$link .= $this->filename;

		return $link;
	}

	private function createDirName(): string
	{
		$name = '';
		if (!$this->isSvg()) {
			if ($this->width !== null) {
				$name .= $this->width;
			}
			if ($this->height !== null) {
				$name .= 'x' . $this->height;
			}

			if ($this->quality !== null) {
				$name .= 'q' . $this->quality;
			}

			if ($this->flag !== null) {
				$name .= '_' . $this->flag;
			}
		}
		if ($name === '') {
			$name = 'orig';
		}

		return $name;
	}
}