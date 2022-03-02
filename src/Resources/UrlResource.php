<?php

declare(strict_types=1);

namespace NAttreid\ImageStorage\Resources;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use Psr\Http\Message\ResponseInterface;

final class UrlResource extends FileResource
{
	private ?ResponseInterface $response = null;
	private int $timeout;

	public function __construct(string $url, string $filename = null)
	{
		parent::__construct($url, $filename);
	}

	public function setTimeOut(int $timeout): void
	{
		$this->timeout = $timeout;
	}

	private function getResponse(): ?ResponseInterface
	{
		if ($this->response === null) {
			$client = new Client([
				'timeout' => $this->timeout
			]);
			try {
				$this->response = $client->head($this->file);
			} catch (ClientException|ConnectException|ServerException $ex) {
			}
		}
		return $this->response;
	}

	public function isOk(): bool
	{
		$response = $this->getResponse();
		if ($response && $response->getStatusCode() === 200) {
			return true;
		}
		return false;
	}

	protected function getContentType(): ?string
	{
		if ($this->isOk() && $this->type === null) {
			$response = $this->getResponse();
			$this->type = $response->getHeader('Content-Type')[0];
		}
		return $this->type;
	}
}