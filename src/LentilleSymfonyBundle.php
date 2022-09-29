<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle;

use Lentille\SymfonyBundle\DependencyInjection\LentilleSymfonyExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LentilleSymfonyBundle extends Bundle {
	public function getContainerExtension(): ?ExtensionInterface {
		if($this->extension === null) {
			$this->extension = new LentilleSymfonyExtension();
		}
		return $this->extension;
	}
}
