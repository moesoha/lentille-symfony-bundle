<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class FrontendVisible {
	public function __construct(
		public readonly array $instances = [],
		public readonly array $attribute = []
	) { }
}
