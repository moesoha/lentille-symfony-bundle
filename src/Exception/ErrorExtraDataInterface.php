<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Exception;

interface ErrorExtraDataInterface {
	public function getExtraData(): array;
}
