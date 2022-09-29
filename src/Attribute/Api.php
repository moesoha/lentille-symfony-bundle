<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Attribute;

use \Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Api {}
