<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Frontend\ConfigEntry;

use HaydenPierce\ClassFinder\ClassFinder;
use Lentille\SymfonyBundle\Attribute\AsExportableEnum;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExportableEnumEntry implements ConfigEntryInterface {
	private readonly array $availableServices;

	public function __construct(
		private readonly array $enumNamespaces = [],
		?TranslatorInterface $translator = null
	) {
		$this->availableServices = [
			TranslatorInterface::class => $translator
		];
	}

	public function getConfig(string $instance): array {
		$result = [];
		foreach(array_unique(array_reduce(
			$this->enumNamespaces,
			fn($a, $n) => array_merge($a, ClassFinder::getClassesInNamespace($n, ClassFinder::RECURSIVE_MODE)),
			[]
		)) as $class) {
			try {
				$enum = new \ReflectionEnum($class);
			} catch (\ReflectionException) {
				continue;
			}
			/** @var AsExportableEnum $attr */
			if(!($attr = ($enum->getAttributes(AsExportableEnum::class)[0] ?? null)?->newInstance())) continue;
			if(!empty($attr->instances) && !in_array($instance, $attr->instances)) continue;

			$cases = [];
			foreach($enum->getCases() as $case) {
				$a = [
					'type' => $case->getName(),
					'id' => $case instanceof \ReflectionEnumBackedCase ? $case->getBackingValue() : $case->getName()
				];

				foreach($attr->extraAttrs as $key => $values) {
					$a[$key] = $values[$case->getName()] ?? null;
				}
				foreach($attr->methodAttrs as $key => $methodName) {
					$a[$key] = $this->callMethodWithArguments($enum->getMethod($methodName), $case->getValue());
				}
				$cases[(string)$a['id']] = $a;
			}
			$result[$attr->name ?: $enum->getShortName()] = $cases;
		}
		return $result;
	}

	private function callMethodWithArguments(\ReflectionMethod $method, \UnitEnum $context): mixed {
		return $method->invokeArgs($context, array_map(
			fn(\ReflectionParameter $param) => array_key_exists($serv = $param->getType()->getName(), $this->availableServices)
				? $this->availableServices[$serv]
				: $param->getDefaultValue(),
			$method->getParameters()
		));
	}
}
