<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Controller;

use Lentille\SymfonyBundle\Exception\FormErrorException;
use Lentille\SymfonyBundle\Frontend\FrontendRendererInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

class AbstractController extends SymfonyAbstractController {
	public static function getSubscribedServices(): array {
		return parent::getSubscribedServices() + [
			FrontendRendererInterface::class
		];
	}

	public function renderFrontend(string $view, array $data = [], int $status = 200, array $headers = [], array $context = []): Response {
		/** @var FrontendRendererInterface $renderer */
		$renderer = $this->container->get(FrontendRendererInterface::class);
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

	protected function assertValidSubmittedForm(FormInterface $form): void {
		if (!$form->isSubmitted() || !$form->isValid()) {
			throw FormErrorException::createFromForm($form);
		}
	}
}
