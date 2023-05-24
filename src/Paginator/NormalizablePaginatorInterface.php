<?php

namespace Lentille\SymfonyBundle\Paginator;

interface NormalizablePaginatorInterface {
	public function getResult(): array;
	public function getPerPage(): int;
	public function getCount(): int;
}
