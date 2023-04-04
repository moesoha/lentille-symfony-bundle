<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Paginator;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DoctrinePaginator implements NormalizableInterface {
	private readonly Paginator $paginator;
	private ?array $result = null;
	private readonly int $page;

	public function __construct(
		Query|QueryBuilder $query,
		private readonly int $limit,
		int $page
	) {
		if($query instanceof QueryBuilder) {
			$query = $query->getQuery();
		}
		$this->page = max(1, $page);
		$query
			->setFirstResult(($this->page - 1) * $this->limit)
			->setMaxResults($this->limit)
		;
		$this->paginator = new Paginator($query);
	}

	public function getPerPage(): int {
		return $this->limit;
	}

	public function getCount(): int {
		return $this->paginator->count();
	}

	public function getResult(): array {
		if($this->result === null) {
			$this->result = iterator_to_array($this->paginator);
		}
		return $this->result;
	}

	public function normalize(NormalizerInterface $normalizer, string $format = null, array $context = []): array {
		return [
			'perPage' => $this->getPerPage(),
			'count' => $this->getCount(),
			'result' => $normalizer->normalize($this->getResult(), $format, $context)
		];
	}
}
