<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Form\Type;

use DateTimeInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class TimestampType extends AbstractType {
	public function getParent(): string {
		return TextType::class;
	}

	public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder->addModelTransformer(
			new CallbackTransformer(
				fn(?DateTimeInterface $object) => $object?->getTimestamp(),
				fn($value) => !empty($value) ? (new \DateTime())->setTimestamp((int)$value) : null
			)
		);
	}
}
