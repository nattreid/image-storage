<?php

declare(strict_types=1);

namespace NAttreid\ImageStorage\Resources;

use Nette\Utils\Strings;

/**
 * Class UrlResource
 *
 * @author Attreid <attreid@gmail.com>
 */
class UrlResource extends FileResource
{
	/** @var string[]|false */
	private $headers;

	public function __construct(string $url)
	{
		parent::__construct($url);
	}

	private function getHeaders()
	{
		if ($this->headers === null) {
			$this->headers = @get_headers($this->file, 1);
		}
		return $this->headers;
	}

	public function isOk(): bool
	{
		$headers = $this->getHeaders();
		if ($headers && Strings::contains($headers[0], '200 OK')) {
			return true;
		}
		return false;
	}

	protected function getContentType(): ?string
	{
		if ($this->isOk() && $this->type === null) {
			$headers = $this->getHeaders();
			$this->type = $headers['Content-Type'];
		}
		return $this->type;
	}
}