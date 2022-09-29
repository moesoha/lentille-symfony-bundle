<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[AsEventListener(RequestEvent::class, 'onKernelRequest', 100)]
class JsonRequestSubscriber {
	public function onKernelRequest(RequestEvent $event) {
		$request = $event->getRequest();
		$content = trim((string)$request->getContent());
		if($request->getContentTypeFormat() !== 'json' || empty($content)) {
			return;
		}
		try {
			$data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
			if(is_array($data)) {
				$request->request->replace($data);
			}
		} catch(\JsonException $e) {
			throw new BadRequestException('Cannot parse JSON request body: '.$e->getMessage());
		}
	}
}
