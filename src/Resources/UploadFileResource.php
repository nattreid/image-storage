<?php

declare(strict_types=1);

namespace NAttreid\ImageStorage\Resources;

use Nette\Http\FileUpload;

/**
 * @property-read FileUpload $file
 */
final class UploadFileResource extends FileResource
{
	protected FileUpload $file;

	public function __construct(FileUpload $file)
	{
		parent::__construct((string)$file, $file->getSanitizedName());
	}

	protected function getContentType(): ?string
	{
		return $this->file->getContentType();
	}

	public function isOk(): bool
	{
		return $this->file->isOk();
	}
}