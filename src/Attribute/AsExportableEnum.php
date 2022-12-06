<?php

namespace Lentille\SymfonyBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AsExportableEnum {
	public const TAG = 'lentille.attribute.exportable_enum';

	/**
	 * @param string|null $name config key name
	 * @param array<string, callable-string> $methodAttrs additional attribute of cases get by method
	 * @param array<string, array> $extraAttrs additional attribute defined directly
	 * @param string[] $instances available in these instances
	 */
	public function __construct(
		public readonly ?string $name = null,
		public readonly array $methodAttrs = [],
		public readonly array $extraAttrs = [],
		public readonly array $instances = []
	) {}
}
