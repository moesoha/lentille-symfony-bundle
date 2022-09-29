<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Serializer\LeveledNormalizer;

use Lentille\SymfonyBundle\Serializer\LeveledNormalizer\NormalizeLevel;

class PinNormalizeLevel {
	/**
	 * @param mixed $object
	 * @param array<class-string, NormalizeLevel> $levels
	 * @param array<class-string, array> $parameters
	 */
	public function __construct(
		public readonly mixed $object,
		public readonly array $levels,
		public readonly array $parameters = []
	) { }
}
