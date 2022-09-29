<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Serializer\LeveledNormalizer;

enum NormalizeLevel: int {
	const MaskLittle = 0b0001;
	const MaskNormal = 0b0010;
	const MaskDetail = 0b0100;

	case Little = self::MaskLittle;
	case Normal = self::MaskLittle | self::MaskNormal;
	case Detail = self::MaskLittle | self::MaskNormal | self::MaskDetail;
}
