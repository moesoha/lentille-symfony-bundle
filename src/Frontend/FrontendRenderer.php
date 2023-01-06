<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Frontend;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;

class FrontendRenderer {
	private const DATA_TYPE_HEADER = 'x-lentille-request';
	private const RESPONSE_HEADERS = [
		'Vary' => self::DATA_TYPE_HEADER
	];

	public function __construct(
		private readonly RequestStack $requestStack,
		private readonly ?TokenStorageInterface $tokenStorage,
		private readonly ?FrontendInitialRendererInterface $initialRenderer,
		#[Autowire(service: 'serializer')] private readonly SerializerInterface $serializer
	) {}

	public function render(string $template, array $data = [], int $status = 200, array $headers = [], array $context = []): Response {
		$instance = 'main';
		if($colon = mb_strpos($template, ':')) {
			$instance = mb_substr($template, 0, $colon);
			$template = mb_substr($template, $colon + 1);
		}

		if(empty($data)) $data[':'] = 0;
		$request = $this->requestStack->getCurrentRequest();
		$responseHeader = $headers + self::RESPONSE_HEADERS;
		$contentOnly = strtolower($request->headers->get(self::DATA_TYPE_HEADER, '')) === 'content-only';

		$frontendData = $this->serializer->serialize([
			'instance' => $instance,
			'template' => $template,
			'status' => $status,
			'locale' => $request->getLocale(),
			'data' => $data,
			'user' => $this->tokenStorage?->getToken()?->getUser(),
			'time' => microtime(true)
		], 'json', array_merge([
			'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
		], $context));

		return ($contentOnly || !$this->initialRenderer)
			? new JsonResponse($frontendData, $status, $responseHeader, true)
			: $this->initialRenderer->render(
				$template,
				[
					'_fe_data' => $frontendData,
					'_fe_instance' => $instance,
				] + $data,
				$status,
				$responseHeader,
				$context
			);
	}
}
