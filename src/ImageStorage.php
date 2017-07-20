<?php

declare(strict_types=1);

namespace NAttreid\ImageStorage;

use NAttreid\ImageStorage\Resources\FileResource;
use NAttreid\ImageStorage\Resources\Resource;
use NAttreid\ImageStorage\Resources\UploadFileResource;
use Nette\Http\FileUpload;

/**
 * Class ImageStorage
 *
 * @author Attreid <attreid@gmail.com>
 */
class ImageStorage
{
	/** @var string */
	private $path;

	/** @var string */
	private $namespace;

	/** @var ImageFactory */
	private $imageFactory;

	public function __construct(string $path, ImageFactory $imageFactory)
	{
		$this->path = $path;
		$this->imageFactory = $imageFactory;
	}

	public function setNamespace(string $namespace)
	{
		$this->namespace = $namespace;
	}

	public function createUploadedResource(FileUpload $fileUpload): ?FileResource
	{
		$resource = null;
		if ($fileUpload->isOk()) {
			$resource = new UploadFileResource($fileUpload);
			$resource->setNamespace($this->namespace);
		}
		return $resource;
	}

	public function createResource(string $file): FileResource
	{
		$resource = new FileResource($file);
		$resource->setNamespace($this->namespace);
		return $resource;
	}

	public function save(FileResource $resource): bool
	{
		if ($resource->isOk() && $resource->isImage()) {
			$resource->checkIdentifier($this->path);
			if ($resource instanceof UploadFileResource) {
				$resource->file->move($this->path . '/' . $resource->getIdentifier());
				return true;
			} else {
				$source = $this->path . '/' . $resource->getIdentifier();
				@mkdir(dirname($source), 0777, true);

				return @copy($resource->file, $source);
			}
		}
		return false;
	}

	public function delete($identifier): bool
	{

	}

	public function getResource($identifier, string $size = null, string $flag = null, int $quality): Resource
	{
		$namespace = substr($identifier, 0, strrpos($identifier, '/'));
		$resource = new Resource($this->path . '/' . $identifier);
		$resource->setSize($size);
		$resource->setFlag($flag);
		$resource->setQuality($quality);
		$resource->setNamespace($namespace);
		return $resource;
	}

	public function link(Resource $resource): string
	{
		return $this->imageFactory->create($resource);
	}

}