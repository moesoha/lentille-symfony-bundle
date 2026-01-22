<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Exception;

interface ErrorExtraDataNormalizableInterface extends ErrorExtraDataInterface {
	public function getExtraDataNormalizeContext(): array;
}
