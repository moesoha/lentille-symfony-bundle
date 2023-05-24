<?php

namespace Lentille\SymfonyBundle\Paginator;

class ManualPaginator implements NormalizablePaginatorInterface {
	private readonly array $result;
	private readonly int $count;
	private readonly int $limit;

	public function __construct(
		array $result,
		?int $count = null,
		?int $limit = null,
	) {
		$this->count = $count === null ? count($result) : $count;
		$this->limit = $limit === null ? count($result) : $limit;
		$this->result = $result;
	}

	public function getPerPage(): int {
		return $this->limit;
	}

	public function getCount(): int {
		return $this->count;
	}

	public function getResult(): array {
		return $this->result;
	}
}
