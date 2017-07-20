<?php

declare(strict_types=1);

namespace NAttreid\ImageStorage;

use NAttreid\ImageStorage\Resources\FileResource;
use NAttreid\ImageStorage\Resources\Resource;
use NAttreid\ImageStorage\Resources\UploadFileResource;
use NAttreid\ImageStorage\Resources\UrlResource;
use Nette\Http\FileUpload;
use Nette\Utils\Finder;

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

	/** @var string */
	private $publicDir;

	public function __construct(string $path, string $publicDir, ImageFactory $imageFactory)
	{
		$this->path = $path;
		$this->publicDir = $publicDir;
		$this->imageFactory = $imageFactory;
	}

	public function setNamespace(?string $namespace)
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

	public function createUrlResource(string $url): FileResource
	{
		$resource = new UrlResource($url);
		$resource->setNamespace($this->namespace);
		return $resource;
	}

	public function createResource(string $file, string $filename = null): FileResource
	{
		$resource = new FileResource($file, $filename);
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

			} elseif ($resource instanceof UrlResource) {
				$ctx = stream_context_create([
					'http' => ['timeout' => 10]
				]);
				$data = @file_get_contents($resource->file, false, $ctx);
				if ($data) {
					$source = $this->path . '/' . $resource->getIdentifier();
					@mkdir(dirname($source), 0777, true);
					file_put_contents($source, $data);
					return true;
				}
				return false;

			} elseif ($resource instanceof Resource) {
				$source = $this->path . '/' . $resource->getIdentifier();
				@mkdir(dirname($source), 0777, true);
				return @rename($resource->file, $source);

			} else {
				$source = $this->path . '/' . $resource->getIdentifier();
				@mkdir(dirname($source), 0777, true);

				return @copy($resource->file, $source);
			}
		}
		return false;
	}

	/**
	 * @param string[]|string $identifiers
	 */
	public function delete($identifiers): void
	{
		if (!is_array($identifiers)) {
			$identifiers = [$identifiers];
		}
		foreach ($identifiers as $identifier) {
			$resource = $this->getResource($identifier);
			$path = $this->publicDir . '/' . $resource->namespace;
			if (file_exists($path)) {
				foreach (Finder::findFiles($resource->filename)->from($path) as $file) {
					@unlink((string) $file);
				}
			}
			@unlink($resource->file);
		}
	}

	public function getResource(?string $identifier, string $size = null, string $flag = null, int $quality = null): Resource
	{
		$resource = new Resource($this->path . '/' . $identifier);
		$resource->setSize($size);
		$resource->setFlag($flag);
		$resource->setQuality($quality);

		if ($identifier !== null) {
			$namespace = $this->imageFactory->parseNamespace($identifier);
			$resource->setNamespace($namespace);
		}

		return $resource;
	}

	public function link(Resource $resource): string
	{
		return $this->imageFactory->create($resource);
	}

}