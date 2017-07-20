<?php

declare(strict_types=1);

namespace NAttreid\ImageStorage\Resources;

use NAttreid\Utils\Strings;
use Nette\Utils\Image;

/**
 * Class Resource
 *
 * @property-read ?int $width
 * @property-read ?int $height
 * @property-read ?string $flag
 * @property-read ?int $quality
 *
 * @author Attreid <attreid@gmail.com>
 */
class Resource extends FileResource
{
	/** @var int */
	private $width;

	/** @var int */
	private $height;

	/** @var string */
	private $flag;

	/** @var int */
	private $quality;

	public function setQuality(?int $quality): void
	{
		$this->quality = $quality;
	}

	public function setSize(?string $size): void
	{
		list($width, $height) = explode('x', $size);
		$this->width = intval($width) ?: null;
		$this->height = intval($height) ?: null;
	}

	public function setFlag(?string $flag): void
	{
		$this->flag = Image::${Strings::upper($flag)};
	}

	protected function getQuality(): ?int
	{
		return $this->quality;
	}

	protected function getSize(): ?string
	{
		return $this->size;
	}

	protected function getFlag(): ?string
	{
		return $this->flag;
	}

	public function createLink(): string
	{
		$link = $this->namespace . '/';

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

		return $name;
	}
}