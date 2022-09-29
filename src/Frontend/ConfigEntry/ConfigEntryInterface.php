<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Frontend\ConfigEntry;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: ['lentille.frontend_config.entry'])]
interface ConfigEntryInterface {
	public function getConfig(string $instance): array;
}

