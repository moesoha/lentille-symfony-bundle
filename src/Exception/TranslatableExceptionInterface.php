<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Exception;

interface TranslatableExceptionInterface {
	public function getMessageKey(): string;
	public function getMessageData(): array;
	public function getMessageDomain(): ?string;
}
