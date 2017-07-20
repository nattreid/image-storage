<?php

declare(strict_types=1);

namespace NAttreid\ImageStorage\Resources;

use Nette\Http\FileUpload;

/**
 * Class UploadResource
 *
 * @property-read FileUpload $file
 *
 * @author Attreid <attreid@gmail.com>
 */
class UploadFileResource extends FileResource
{
	/** @var FileUpload */
	protected $file;

	public function __construct(FileUpload $file)
	{
		parent::__construct($file, $file->getSanitizedName());
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