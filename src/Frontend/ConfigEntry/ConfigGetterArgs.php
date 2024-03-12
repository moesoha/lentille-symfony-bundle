<?php

namespace Lentille\SymfonyBundle\Frontend\ConfigEntry;

class ConfigGetterArgs {
	public function __construct(
		public readonly string $instance,
		public readonly string $locale,
		public readonly array $instanceVisible,
		public readonly array $instanceVisibleTo
	) {}
}
