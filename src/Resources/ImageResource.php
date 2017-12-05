<?php

declare(strict_types=1);

namespace NAttreid\ImageStorage\Resources;

use NAttreid\Utils\Strings;
use Nette\Utils\Image;

/**
 * Class ImageResource
 *
 * @property-read int|null $width
 * @property-read int|null $height
 * @property-read int|null $flag
 * @property-read int|null $quality
 *
 * @author Attreid <attreid@gmail.com>
 */
class ImageResource extends FileResource
{
	/** @var int */
	private $width;

	/** @var int */
	private $height;

	/** @var int */
	private $flag;

	/** @var int */
	private $quality;

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
		$this->flag = @constant(Image::class . '::' . trim(Strings::upper($flag)));
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