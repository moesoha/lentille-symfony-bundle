<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Paginator;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class DoctrinePaginator implements NormalizablePaginatorInterface {
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
}
