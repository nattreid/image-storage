<?php

declare(strict_types=1);

namespace NAttreid\ImageStorage;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use NAttreid\ImageStorage\Resources\FileResource;
use NAttreid\ImageStorage\Resources\ImageResource;
use NAttreid\ImageStorage\Resources\UploadFileResource;
use NAttreid\ImageStorage\Resources\UrlResource;
use Nette\Http\FileUpload;
use Nette\Utils\Finder;
use Nette\Utils\UnknownImageFileException;

final class ImageStorage
{
	private string $path;
	private string $namespace;
	private ?string $domain;
	private ImageFactory $imageFactory;
	private string $publicDir;
	private int $timeout;

	public function __construct(string $path, string $publicDir, ?string $domain, int $timeout, ImageFactory $imageFactory)
	{
		$this->path = $path;
		$this->publicDir = $publicDir;
		$this->imageFactory = $imageFactory;
		$this->domain = $domain;
		$this->timeout = $timeout;
	}

	public function setNamespace(string $namespace = null): void
	{
		$this->namespace = $namespace;
	}

	public function setDomain(string $domain = null): void
	{
		$this->domain = $domain;
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
		$resource->setTimeOut($this->timeout);
		return $resource;
	}

	public function createResource(string $file, string $filename = null): FileResource
	{
		$resource = new FileResource($file, $filename);
		$resource->setNamespace($this->namespace);
		return $resource;
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

	public function getResource(?string $identifier, string $size = null, string $flag = null, int $quality = null): ImageResource
	{
		$resource = new ImageResource($this->path . '/' . $identifier);
		$resource->setSize($size);
		$resource->setFlag($flag);
		$resource->setQuality($quality);

		if ($identifier !== null) {
			$namespace = $this->imageFactory->parseNamespace($identifier);
			$resource->setNamespace($namespace);
		}

		return $resource;
	}

	/**
	 * @throws UnknownImageFileException
	 */
	public function link(ImageResource $resource): string
	{
		return $this->imageFactory->create($resource, $this->domain);
	}

	public function save(FileResource $resource): bool
	{
		if ($resource->isOk() && $resource->isImage()) {
			$resource->checkIdentifier($this->path);
			if ($resource instanceof UploadFileResource) {
				return $this->processUploadFileResource($resource);
			} elseif ($resource instanceof UrlResource) {
				return $this->processUrlResource($resource);
			} elseif ($resource instanceof ImageResource) {
				return $this->processImageResource($resource);
			} else {
				return $this->copyResource($resource);
			}
		}
		return false;
	}

	public function copy(ImageResource $resource): bool
	{
		if ($resource->isOk() && $resource->isImage()) {
			$resource->checkIdentifier($this->path);
			return $this->copyResource($resource);
		}
		return false;
	}

	private function processUploadFileResource(UploadFileResource $resource): bool
	{
		$resource->file->move($this->path . '/' . $resource->getIdentifier());
		return true;
	}

	private function processUrlResource(UrlResource $resource): bool
	{
		$client = new Client([
			'timeout' => $this->timeout
		]);
		try {
			$response = $client->get($resource->file);
			$data = $response->getBody()->getContents();
			if ($data) {
				$source = $this->path . '/' . $resource->getIdentifier();
				@mkdir(dirname($source), 0777, true);
				file_put_contents($source, $data);
				return true;
			}
		} catch (ClientException|ConnectException|ServerException $ex) {
		}
		return false;
	}

	private function processImageResource(ImageResource $resource): bool
	{
		$source = $this->path . '/' . $resource->getIdentifier();
		@mkdir(dirname($source), 0777, true);
		return @rename($resource->file, $source);
	}

	private function copyResource(FileResource $resource): bool
	{
		$source = $this->path . '/' . $resource->getIdentifier();
		@mkdir(dirname($source), 0777, true);
		return @copy($resource->file, $source);
	}
}