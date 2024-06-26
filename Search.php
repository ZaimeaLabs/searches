<?php

declare(strict_types=1);

namespace ZaimeaLabs\Searches;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \ZaimeaLabs\Searches\Builder new()
 * @method static \ZaimeaLabs\Searches\Builder orderByAsc()
 * @method static \ZaimeaLabs\Searches\Builder orderByDesc()
 * @method static \ZaimeaLabs\Searches\Builder dontParseTerm()
 * @method static \ZaimeaLabs\Searches\Builder includeModelType()
 * @method static \ZaimeaLabs\Searches\Builder beginWithWildcard(bool $state)
 * @method static \ZaimeaLabs\Searches\Builder endingWithWildcard(bool $state)
 * @method static \ZaimeaLabs\Searches\Builder soundsLike(bool $state)
 * @method static \ZaimeaLabs\Searches\Builder in($query, $columns, string $orderByColumn = null)
 * @method static \ZaimeaLabs\Searches\Builder inMany($queries)
 * @method static \ZaimeaLabs\Searches\Builder when($value, callable $callback = null, callable $default = null)
 * @method static \ZaimeaLabs\Searches\Builder paginate($perPage = 15, $pageName = 'page', $page = null)
 * @method static \ZaimeaLabs\Searches\Builder simplePaginate($perPage = 15, $pageName = 'page', $page = null)
 * @method static \Illuminate\Support\Collection parseTerms(string $terms, callable $callback = null)
 * @method static \Illuminate\Database\Eloquent\Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator get(string $terms = null)
 *
 * @see \ZaimeaLabs\Searches\Builder
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
