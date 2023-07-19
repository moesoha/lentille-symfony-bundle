<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Exception;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class FormErrorException extends BadRequestHttpException implements ErrorExtraDataInterface {
	private array $errorFields = [];
	private array $extraData = [];

	static function createFromForm(FormInterface $form): self {
		$e = new self('Form is not valid.');
		foreach($form->getErrors(true) as $error) {
			$e->errorFields[] = [
				'name' => $error->getOrigin()->getName(),
				'value' => $error->getMessageParameters()['{{ value }}'] ?? null,
				'message' => $error->getMessage()
			];
		}
		if($e->extraData['submitted'] = $form->isSubmitted()) {
			$e->extraData['valid'] = $form->isValid();
		}
		if(!empty($formName = $form->getName())) {
			$e->extraData['name'] = $formName;
		}
		return $e;
	}

	public function getExtraData(): array {
		return array_merge($this->extraData, [
			'lentilleFormError' => 1,
			'fields' => $this->errorFields
		]);
	}
}
