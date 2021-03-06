<?php

declare(strict_types=1);

namespace NAttreid\ImageStorage\Resources;

use NAttreid\Utils\File;
use NAttreid\Utils\Strings;
use Nette\InvalidArgumentException;
use Nette\SmartObject;
use Nette\Utils\Random;

/**
 * Class FileResource
 *
 * @property-read string $file
 * @property-read string $filename
 * @property-read string $namespace
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
	protected $type;

	public function __construct($file, string $filename = null)
	{
		$this->file = $file;
		$this->filename = $filename ?? trim(Strings::webalize(basename($this->file), '.', false), '.-');
	}

	protected function getFile()
	{
		return $this->file;
	}

	protected function getNamespace(): ?string
	{
		return $this->namespace;
	}

	protected function getFilename(): string
	{
		return $this->filename;
	}

	public function getIdentifier(): string
	{
		$identifier = $this->namespace;
		if ($identifier) {
			$identifier .= '/';
		}
		$identifier .= $this->filename;
		return $identifier;
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
		if ($this->type === null) {
			if ($this->isOk()) {
				$this->type = @finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->file) ?: null;
			} else {
				$this->type = Strings::endsWith($this->filename, '.svg') ? 'image/svg+xml' : null;
			}
		}
		return $this->type;
	}

	public function isOk(): bool
	{
		return file_exists($this->file);
	}

	public function isValid(): bool
	{
		return File::isImageValid($this->file);
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
		$this->namespace = $namespace;
	}

	public function isImage(): bool
	{
		return in_array($this->getContentType(), ['image/gif', 'image/png', 'image/jpeg', 'image/svg+xml'], true);
	}

	public function isSvg(): bool
	{
		return $this->getContentType() === 'image/svg+xml';
	}

	public function __toString()
	{
		return $this->getIdentifier();
	}
}