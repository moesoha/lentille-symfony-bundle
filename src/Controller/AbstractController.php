<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Controller;

use Lentille\SymfonyBundle\Frontend\FrontendRenderer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;

class AbstractController extends SymfonyAbstractController {
	public static function getSubscribedServices(): array {
		return parent::getSubscribedServices() + [
			FrontendRenderer::class
		];
	}

	public function renderFrontend(string $view, array $data = [], int $status = 200, array $headers = [], array $context = []): Response {
		/** @var FrontendRenderer $renderer */
		$renderer = $this->container->get(FrontendRenderer::class);
		return $renderer->render($view, $data, $status, $headers, $context);
	}

	protected function createFormBuilder($data = null, array $options = []): FormBuilderInterface {
		if(!array_key_exists('name', $options)) {
			return parent::createFormBuilder($data, $options);
		}
		$formName = $options['name'] ?: '';
		unset($options['name']);
		return $this->container->get('form.factory')->createNamedBuilder($formName, FormType::class, $data, $options);
	}

	protected function createNamedFormBuilder(
		$data = null,
		$type = FormType::class,
		$name = '',
		array $options = []
	): FormBuilderInterface {
		return $this->container->get('form.factory')->createNamedBuilder($name, $type, $data, $options);
	}
}
