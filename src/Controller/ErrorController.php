<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Controller;

use Lentille\SymfonyBundle\Attribute\Api;
use Lentille\SymfonyBundle\Exception\ErrorExtraDataInterface;
use Lentille\SymfonyBundle\Exception\TranslatableExceptionInterface;
use Lentille\SymfonyBundle\Frontend\FrontendRendererInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
class ErrorController {
	public function __construct(
		#[Autowire(value: '%kernel.environment%')] private readonly string $environment,
		private readonly ?TranslatorInterface $translator,
		private readonly ?AuthorizationCheckerInterface $authorizationChecker,
		private readonly ?FrontendRendererInterface $renderer,
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
			$responseHeaders = array_merge($responseHeaders, $exception->getHeaders());
		} else if (!$showTrace && !($exception instanceof \InvalidArgumentException)) {
			$data['errorMessage'] = 'Internal Error';
		}

		if($showTrace) {
			$data['errorTrace'] = $exception->getTraceAsString();
			$exex = $exception;
			while($exex = $exex->getPrevious()) {
				$data['errorTrace'] .= "\n\n========== ".$exex::class." ==========\n";
				$data['errorTrace'] .= $exex->getTraceAsString();
			}
		}
		if($exception instanceof AuthenticationException) {
			$data['errorCode'] = Response::HTTP_UNAUTHORIZED;
			$data['errorMessage'] = $this->trans(
				$exception->getMessageKey(),
				$exception->getMessageData(),
				'security'
			);
		} else if($exception instanceof TranslatableExceptionInterface) {
			$data['errorMessage'] = $this->trans(
				$exception->getMessageKey(),
				$exception->getMessageData(),
				$exception->getMessageDomain() ?: 'exceptions'
			);
		} else {
			$data['errorMessage'] = $this->trans($data['errorMessage']);
		}

		return $useJson
			? new JsonResponse($data, $data['errorCode'], $responseHeaders)
			: $this->render($data, $data['errorCode'], $responseHeaders)
		;
	}

	private function trans(string $key, array $data = [], string $domain = 'exceptions'): string {
		if(!$this->translator) return strtr($key, $data);
		return $this->translator->trans($key, $data, $domain);
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
		if($this->renderer?->isContentRequest($request)) return false;
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
