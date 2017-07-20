<?php

declare(strict_types=1);

namespace NAttreid\ImageStorage\Resources;

use NAttreid\Utils\Strings;
use Nette\InvalidArgumentException;
use Nette\SmartObject;
use Nette\Utils\Random;

/**
 * Class FileResource
 *
 * @property-read string $file
 *
 * @author Attreid <attreid@gmail.com>
 */
class FileResource
{
	use SmartObject;

	/** @var mixed */
	protected $file;

	/** @var string */
	protected $filename;

	/** @var string */
	protected $namespace;

	/** @var string */
	private $type;

	public function __construct($file, string $filename = null)
	{
		$this->file = $file;
		$this->filename = $fileName ?? basename($this->file);
	}

	protected function getFile()
	{
		return $this->file;
	}

	public function getIdentifier(): string
	{
		return $this->namespace . '/' . $this->filename;
	}

	public function checkIdentifier(string $path): void
	{
		if (file_exists($path . '/' . $this->getIdentifier())) {
			$this->filename = Random::generate(1) . '_' . $this->filename;
			$this->checkIdentifier($path);
		}
	}

	protected function getContentType(): ?string
	{
		if ($this->isOk() && $this->type === null) {
			$this->type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->file);
		}
		return $this->type;
	}

	public function isOk(): bool
	{
		return file_exists($this->file);
	}

	public function setNamespace(?string $namespace): void
	{
		if ($namespace) {
			if (Strings::contains($namespace, '.')) {
				throw new InvalidArgumentException("Namespace contains invalid character '.'");
			}
			if (
				Strings::startsWith($namespace, '/')
				|| Strings::endsWith($namespace, '/')
			) {
				throw new InvalidArgumentException("Namespace must not begin or end with '/'");
			}
			$this->namespace = Strings::replace($namespace, '/\s+/', '');
		}
		$this->namespace = null;
	}

	public function isImage(): bool
	{
		return in_array($this->getContentType(), ['image/gif', 'image/png', 'image/jpeg', 'image/svg+xml'], true);
	}

	public function __toString()
	{
		return $this->getIdentifier();
	}
}