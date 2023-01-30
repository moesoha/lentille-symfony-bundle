<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Controller;

use Lentille\SymfonyBundle\Attribute\Api;
use Lentille\SymfonyBundle\Exception\ErrorExtraDataInterface;
use Lentille\SymfonyBundle\Exception\TranslatableExceptionInterface;
use Lentille\SymfonyBundle\Frontend\FrontendRenderer;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
class ErrorController {
	public function __construct(
		#[Autowire(value: '%kernel.environment%')] private readonly string $environment,
		private readonly ?TranslatorInterface $translator,
		private readonly ?AuthorizationCheckerInterface $authorizationChecker,
		private readonly ?FrontendRenderer $renderer,
		private readonly array $traceRoles
	) { }

	public function __invoke(RequestStack $requestStack): Response {
		[$request, $errorRequest] = [$requestStack->getMainRequest(), $requestStack->getCurrentRequest()];
		$exception = $errorRequest->attributes->get('exception');
		if(!$exception instanceof \Throwable) {
			throw new \InvalidArgumentException('No exception in Request');
		}
		$useJson = $this->shouldReturnJson($request);
		$showTrace = $this->shouldShowTrace();

		$responseHeaders = [];
		$data = [
			'errorCode' => 500,
			'errorType' => $exception::class,
			'errorMessage' => $exception->getMessage(),
			'errorData' => $exception instanceof ErrorExtraDataInterface ? $exception->getExtraData() : []
		];
		if($exception instanceof HttpExceptionInterface) {
			$data['errorCode'] = $exception->getStatusCode();
			$responseHeaders += $exception->getHeaders();
		} else if (!$showTrace && !($exception instanceof \InvalidArgumentException)) {
			$data['errorMessage'] = 'Internal Error';
		}

		if($showTrace) $data['errorTrace'] = $exception->getTraceAsString();
		if($this->translator) {
			if($exception instanceof TranslatableExceptionInterface) {
				$data['errorMessage'] = $this->translator->trans(
					$exception->getMessageKey(),
					$exception->getMessageData(),
					$exception->getMessageDomain() ?: 'exceptions'
				);
			} else {
				$data['errorMessage'] = $this->translator->trans($data['errorMessage'], [], 'exceptions');
			}
		}

		return $useJson
			? new JsonResponse($data, $data['errorCode'], $responseHeaders)
			: $this->render($data, $data['errorCode'], $responseHeaders)
		;
	}

	protected function render(array $data, int $status, array $headers): Response {
		return $this->renderer->render('error', $data, $status, $headers);
	}

	private function shouldShowTrace(): bool {
		if($this->environment === 'dev') return true;
		if(!$this->authorizationChecker) return false;
		try {
			foreach($this->traceRoles as $role) {
				if($this->authorizationChecker->isGranted($role)) return true;
			}
		} catch(AuthenticationCredentialsNotFoundException) {
		}
		return false;
	}

	private function shouldReturnJson(Request $request): bool {
		if(
			($request->getRequestFormat() === 'json') ||
			($request->getContentTypeFormat() === 'json') ||
			(($request->getAcceptableContentTypes()[0] ?? '') === 'application/json')
		) return true;

		if(
			!empty($actionPath = $request->attributes->get('_controller')) &&
			!empty($actionSegments = explode('::', $actionPath) ?? null)
		) {
			try {
				$controller = new \ReflectionClass($actionSegments[0]);
				if(!empty($controller->getAttributes(Api::class))) {
					return true;
				}
				if(isset($actionSegments[1])) {
					$action = $controller->getMethod($actionSegments[1]);
					if(!empty($action->getAttributes(Api::class))) {
						return true;
					}
				}
			} catch(\ReflectionException) {
			}
		}
		return false;
	}
}
