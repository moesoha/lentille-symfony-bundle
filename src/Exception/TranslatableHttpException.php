<?php

namespace Lentille\SymfonyBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class TranslatableHttpException extends HttpException implements TranslatableExceptionInterface {
	private ?string $messageKey = null;
	private array $messageData = [];

	public function getMessageKey(): string {
		return $this->messageKey ?: $this->getMessage();
	}

	public function getMessageData(): array {
		return $this->messageData;
	}

	public function getMessageDomain(): ?string {
		return null;
	}

	public function setTransMessage(?string $key = null, array $data = []): self {
		$this->messageKey = $key;
		$this->messageData = $data;
		return $this;
	}
}
