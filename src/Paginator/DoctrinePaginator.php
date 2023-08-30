<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Paginator;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class DoctrinePaginator implements NormalizablePaginatorInterface {
	private readonly Criteria $criteria;
	private readonly Paginator|Collection $iterable;
	private ?array $result = null;

	public function __construct(
		Query|QueryBuilder|Paginator|Collection $iterable,
		int $limit, int $page
	) {
		$this->criteria = (new Criteria())
			->setFirstResult((max(1, $page) - 1) * $limit)
			->setMaxResults($limit)
		;
		$this->iterable = self::normalizeIterable($iterable, $this->criteria);
	}

	private static function normalizeIterable(mixed $iterable, Criteria $criteria): Paginator|Collection {
		if($iterable instanceof Collection || $iterable instanceof Paginator) {
			return $iterable;
		}
		if($iterable instanceof QueryBuilder) {
			$iterable = $iterable->getQuery();
		}
		if($iterable instanceof Query) {
			$iterable
				->setFirstResult($criteria->getFirstResult())
				->setMaxResults($criteria->getMaxResults())
			;
			return new Paginator($iterable);
		}
		throw new \InvalidArgumentException('Unexpected iterable to paginate: '.$iterable::class);
	}

	public function getPerPage(): int {
		return $this->criteria->getMaxResults();
	}

	public function getCount(): int {
		return $this->iterable->count();
	}

	public function getResult(): array {
		if($this->result) return $this->result;
		if($this->iterable instanceof Paginator) {
			return $this->result = iterator_to_array($this->iterable);
		}
		if($this->iterable instanceof Selectable) {
			return $this->result = $this->iterable->matching($this->criteria)->toArray();
		}
		if($this->iterable instanceof Collection) {
			return $this->result = $this->iterable->slice(
				$this->criteria->getFirstResult(),
				$this->criteria->getMaxResults()
			);
		}
		throw new \InvalidArgumentException('Unexpected object to getResult: '.$this->iterable::class);
	}
}
