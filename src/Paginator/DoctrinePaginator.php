<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Paginator;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\CountWalker;
use Doctrine\ORM\Tools\Pagination\Paginator;

class DoctrinePaginator implements NormalizablePaginatorInterface {
	public const HINT_DOCTRINE_FETCH_JOIN_COLLECTION = 'lentille.paginator.doctrineFetchJoinCollection';
	public const HINT_DOCTRINE_USE_OUTPUT_WALKERS = 'lentille.paginator.doctrineUseOutputWalkers';
	private readonly Criteria $criteria;
	private readonly Paginator|Collection $iterable;
	private ?array $result = null;

	public function __construct(
		Query|QueryBuilder|Paginator|Collection $iterable,
		int $limit, int $page,
		private readonly ?int $countOverride = null,
		private readonly ?int $countMax = null
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
			$hint = defined(Paginator::class.'::HINT_ENABLE_DISTINCT')
				? Paginator::HINT_ENABLE_DISTINCT
				: 'paginator.distinct.enable'
			;
			if(!$iterable->hasHint($hint)) {
				$iterable->setHint($hint, false);
			}
			if(!$iterable->hasHint(CountWalker::HINT_DISTINCT)) {
				$iterable->setHint(CountWalker::HINT_DISTINCT, false);
			}
			$p = new Paginator($iterable, (bool)$iterable->getHint(self::HINT_DOCTRINE_FETCH_JOIN_COLLECTION));
			if(!$iterable->hasHint(self::HINT_DOCTRINE_USE_OUTPUT_WALKERS)) {
				$p->setUseOutputWalkers(false);
			} else {
				$p->setUseOutputWalkers($iterable->getHint(self::HINT_DOCTRINE_USE_OUTPUT_WALKERS));
			}
			return $p;
		}
		throw new \InvalidArgumentException('Unexpected iterable to paginate: '.$iterable::class);
	}

	public function getPerPage(): int {
		return $this->criteria->getMaxResults();
	}

	public function getCount(): int {
		if($this->countOverride !== null) return $this->countOverride;
		$count = $this->iterable->count();
		if($this->countMax !== null) return min($count, $this->countMax);
		return $count;
	}

	public function getResult(): array {
		if($this->result) return $this->result;
		if($this->iterable instanceof Paginator) {
			return $this->result = iterator_to_array($this->iterable);
		}
		if($this->iterable instanceof Selectable) {
			return $this->result = array_values($this->iterable->matching($this->criteria)->toArray());
		}
		if($this->iterable instanceof Collection) {
			return $this->result = array_values($this->iterable->slice(
				$this->criteria->getFirstResult(),
				$this->criteria->getMaxResults()
			));
		}
		throw new \InvalidArgumentException('Unexpected object to getResult: '.$this->iterable::class);
	}
}
