<?php

declare(strict_types=1);

namespace Zaimea\Searches;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Zaimea\Searches\Builder new()
 * @method static \Zaimea\Searches\Builder orderByAsc()
 * @method static \Zaimea\Searches\Builder orderByDesc()
 * @method static \Zaimea\Searches\Builder dontParseTerm()
 * @method static \Zaimea\Searches\Builder includeModelType()
 * @method static \Zaimea\Searches\Builder beginWithWildcard(bool $state)
 * @method static \Zaimea\Searches\Builder endingWithWildcard(bool $state)
 * @method static \Zaimea\Searches\Builder soundsLike(bool $state)
 * @method static \Zaimea\Searches\Builder in($query, $columns, string $orderByColumn = null)
 * @method static \Zaimea\Searches\Builder inMany($queries)
 * @method static \Zaimea\Searches\Builder when($value, callable $callback = null, callable $default = null)
 * @method static \Zaimea\Searches\Builder paginate($perPage = 15, $pageName = 'page', $page = null)
 * @method static \Zaimea\Searches\Builder simplePaginate($perPage = 15, $pageName = 'page', $page = null)
 * @method static \Illuminate\Support\Collection parseTerms(string $terms, callable $callback = null)
 * @method static \Illuminate\Database\Eloquent\Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator get(string $terms = null)
 *
 * @see \Zaimea\Searches\Builder
 */
class Search extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'searches';
    }
}
