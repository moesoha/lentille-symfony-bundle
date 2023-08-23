<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Serializer\Normalizer;

use Doctrine\Common\Util\ClassUtils;
use Lentille\SymfonyBundle\Serializer\LeveledNormalizer\LeveledNormalizerInterface;
use Lentille\SymfonyBundle\Serializer\LeveledNormalizer\NormalizeLevel;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

#[AutoconfigureTag(attributes: ['priority' => -600])]
class LeveledNormalizer implements NormalizerInterface, SerializerAwareInterface {
	use SerializerAwareTrait;

	public const CONTEXT_LEVEL_MAP = self::class . '+LevelMap';
	public const CONTEXT_PARAMETER = self::class . '+Parameter';

	public function __construct(
		#[TaggedLocator('lentille.serializer.leveled_normalizer')] private readonly ContainerInterface $normalizers
	) {
	}

	private static function getClassName(object $object): string {
		if(class_exists(ClassUtils::class)) {
			return ClassUtils::getClass($object);
		}
		return $object::class;
	}

	public function normalize(mixed $object, string $format = null, array $context = []): mixed {
		$key = '[UnknownObject]';
		if(is_object($object)) {
			/** @var LeveledNormalizerInterface $normalizer */
			$normalizer = $this->normalizers->get($key = self::getClassName($object));
		}
		if(empty($normalizer)) {
			throw new \RuntimeException('Cannot find normalizer for '.$key);
		}
		$param = $context[self::CONTEXT_PARAMETER][$key] ?? [];
		$level = $context[self::CONTEXT_LEVEL_MAP][$key] ?? NormalizeLevel::Little;
		if(!$level instanceof NormalizeLevel) {
			throw new \InvalidArgumentException('Bad normalize level');
		}
		$level = $level->value;
		$data = [];
		if($level & NormalizeLevel::MaskLittle) {
			$data += $normalizer->littleAdditionalData($object, $param);
		}
		if($level & NormalizeLevel::MaskNormal) {
			$data += $normalizer->normalAdditionalData($object, $param);
		}
		if($level & NormalizeLevel::MaskDetail) {
			$data += $normalizer->detailAdditionalData($object, $param);
		}
		return $this->serializer->normalize($data, $format, $context);
	}

	public function getSupportedTypes(?string $format): array {
		return [
			'object' => true,
			'*' => null
		];
	}

	public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool {
		if(is_object($data)) {
			return $this->normalizers->has(self::getClassName($data));
		}
		return false;
	}
}
