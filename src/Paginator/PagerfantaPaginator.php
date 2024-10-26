<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Paginator;

use Pagerfanta\Pagerfanta;

class PagerfantaPaginator implements NormalizablePaginatorInterface {
	public function __construct(
		private readonly Pagerfanta $pager,
		int $limit, int $page,
		private readonly ?int $countOverride = null,
		private readonly ?int $countMax = null
	) {
		$this->pager
			->setNormalizeOutOfRangePages(true)
			->setMaxPerPage($limit)
			->setCurrentPage($page)
		;
		if($this->countMax) {
			$this->pager->setMaxNbPages((int)ceil($countMax / $limit));
		} else {
			$this->pager->resetMaxNbPages();
		}
	}

	public function getPerPage(): int {
		return $this->pager->getMaxPerPage();
	}

	public function getCount(): int {
		if($this->countOverride !== null) return $this->countOverride;
		$count = $this->pager->count();
		if($this->countMax !== null) return min($count, $this->countMax);
		return $count;
	}

	public function getResult(): array {
		return $this->pager->getCurrentPageResults();
	}
}
