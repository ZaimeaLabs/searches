<?php

declare(strict_types=1);

namespace Zaimea\Searches;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use Zaimea\Searches\Exceptions\RelevanceException;
use Zaimea\Searches\SearchThrough;

class Builder
{
    use Conditionable;

    /**
     * Collection of models.
     *
     * @var Collection
     */
    protected Collection $models;

    /**
     * "order by" clause to the query.
     *
     * @var string
     */
    protected string $orderBy;

    /**
     * "order by" model.
     *
     * @var array|null
     */
    protected ?array $orderByModel = null;

    /**
     * Begin the search term with a wildcard.
     *
     * @var bool
     */
    protected bool $beginWithWildcard = true;

    /**
     * End the search term with a wildcard.
     *
     * @var bool
     */
    protected bool $endingWithWildcard = true;

    /**
     * Where operator.
     *
     * @var string
     */
    protected string $whereOperator = 'like';

    /**
     * Use soundex to match the terms.
     *
     * @var bool
     */
    protected bool $soundsLike = false;

    /**
     * Ignore case.
     *
     * @var bool
     */
    protected bool $ignoreCase = false;

    /**
     * Raw input.
     *
     * @var string|null
     */
    protected ?string $rawTerms = null;

    /**
     * Collection of search terms.
     *
     * @var Collection
     */
    protected Collection $terms;

    /**
     * Collection of search terms.
     *
     * @var Collection
     */
    protected Collection $termsWithoutWildcards;

    /**
     * The number of models to return for pagination.
     *
     * @var int
     */
    protected int $perPage = 15;

    /**
     * The query string variable used to store the page.
     *
     * @var string
     */
    protected string $pageName = '';

    /**
     * Parse the search term into multiple terms.
     *
     * @var bool
     */
    protected bool $parseTerm = true;

    /**
     * Use simplePaginate() on Eloquent\Builder vs paginate()
     *
     * @var bool
     */
    protected bool $simplePaginate = false;

    /**
     * Current page.
     *
     * @var int|null
     */
    protected $page;

    /**
     * Initialises the instanace with a fresh Collection and default sort.
     */
    public function __construct()
    {
        $this->models = new Collection;

        $this->orderByAsc();
    }

    /**
     * Sort the results in ascending order.
     *
     * @return self
     */
    public function orderByAsc(): self
    {
        $this->orderBy = 'asc';

        return $this;
    }

    /**
     * Sort the results in descending order.
     *
     * @return self
     */
    public function orderByDesc(): self
    {
        $this->orderBy = 'desc';

        return $this;
    }

    /**
     * Sort the results in relevance order.
     *
     * @return self
     */
    public function orderByRelevance(): self
    {
        $this->orderBy = 'relevance';

        return $this;
    }

    /**
     * Sort the results in order of the given models.
     *
     * @param  array $classes
     * @return self
     */
    public function orderByModel($classes): self
    {
        $this->orderByModel = Arr::wrap($classes);

        return $this;
    }

    /**
     * Disable the parsing of the search term.
     */
    public function dontParseTerm(): self
    {
        $this->parseTerm = false;

        return $this;
    }

    /**
     * Set in witch model to search through.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|string  $query
     * @param  string|array|\Illuminate\Support\Collection   $columns
     * @param  string  $orderByColumn
     * @return self
     */
    public function in($query, $columns = null, ?string $orderByColumn = null): self
    {
        /** @var EloquentBuilder $builder */
        $builder = is_string($query) ? $query::query() : $query;

        if (is_null($orderByColumn)) {
            $model = $builder->getModel();

            $orderByColumn = $model->usesTimestamps()
                ? $model->getUpdatedAtColumn()
                : $model->getKeyName();
        }

        $searchThrough = new SearchThrough(
            $builder,
            Collection::wrap($columns),
            $orderByColumn,
            $this->models->count(),
        );

        $this->models->push($searchThrough);

        return $this;
    }

    /**
     * Loop through the queries and add them.
     *
     * @param  mixed $value
     * @return self
     */
    public function inMany($queries): self
    {
        Collection::make($queries)->each(function ($query) {
            $this->in(...$query);
        });

        return $this;
    }

    /**
     * Set the model full text to search through.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|string  $query
     * @param  string|array|\Illuminate\Support\Collection   $columns
     * @param  array   $options
     * @param  string  $orderByColumn
     * @return self
     */
    public function modelFullText($query, $columns = null, array $options = [], ?string $orderByColumn = null): self
    {
        $builder = is_string($query) ? $query::query() : $query;

        $searchThrough = new SearchThrough(
            $builder,
            Collection::wrap($columns),
            $orderByColumn ?: $builder->getModel()->getUpdatedAtColumn(),
            $this->models->count(),
            true,
            $options
        );

        $this->models->push($searchThrough);

        return $this;
    }

    /**
     * Set the 'orderBy' column of the latest added model.
     *
     * @param  string $orderByColumn
     * @return self
     */
    public function orderBy(string $orderByColumn): self
    {
        $this->models->last()->orderByColumn($orderByColumn);

        return $this;
    }

    /**
     * Ignore case of terms.
     *
     * @param  bool $state
     * @return self
     */
    public function ignoreCase(bool $state = true): self
    {
        $this->ignoreCase = $state;

        return $this;
    }

    /**
     * Let's each search term begin with a wildcard.
     *
     * @param  bool $state
     * @return self
     */
    public function beginWithWildcard(bool $state = true): self
    {
        $this->beginWithWildcard = $state;

        return $this;
    }

    /**
     * Let's each search term end with a wildcard.
     *
     * @param  bool $state
     * @return self
     */
    public function endingWithWildcard(bool $state = true): self
    {
        $this->endingWithWildcard = $state;

        return $this;
    }

    /**
     * Use 'sounds like' operator instead of 'like'.
     *
     * @return self
     */
    public function soundsLike(bool $state = true): self
    {
        $this->soundsLike = $state;

        $this->whereOperator = $state ? 'sounds like' : 'like';

        return $this;
    }

    /**
     * Sets the pagination properties.
     *
     * @param  integer  $perPage
     * @param  string   $pageName
     * @param  int|null $page
     * @return self
     */
    public function paginate($perPage = 15, $pageName = 'page', $page = null): self
    {
        $this->page           = $page ?: Paginator::resolveCurrentPage($pageName);
        $this->pageName       = $pageName;
        $this->perPage        = $perPage;
        $this->simplePaginate = false;

        return $this;
    }

    /**
     * Paginate using simple pagination.
     *
     * @param  integer  $perPage
     * @param  string   $pageName
     * @param  int|null $page
     * @return self
     */
    public function simplePaginate($perPage = 15, $pageName = 'page', $page = null): self
    {
        $this->paginate($perPage, $pageName, $page);

        $this->simplePaginate = true;

        return $this;
    }

    /**
     * Parse the terms and loop through them with the optional callable.
     *
     * @param  string   $terms
     * @param  callable $callback
     * @return \Illuminate\Support\Collection
     */
    public function parseTerms(string $terms, ?callable $callback = null): Collection
    {
        $callback = $callback ?: fn () => null;

        return Collection::make(str_getcsv($terms, ' ', '"'))
            ->filter()
            ->values()
            ->when($callback !== null, function ($terms) use ($callback) {
                return $terms->each(fn ($value, $key) => $callback($value, $key));
            });
    }

    /**
     * Creates a collection out of the given search term.
     *
     * @param  string $terms
     * @return self
     */
    protected function initializeTerms(string $terms): self
    {
        $this->rawTerms = $terms;

        $terms = $this->parseTerm ? $this->parseTerms($terms) : $terms;

        $this->termsWithoutWildcards = Collection::wrap($terms)->filter()->map(function ($term) {
            return $this->ignoreCase ? Str::lower($term) : $term;
        });

        $this->terms = Collection::make($this->termsWithoutWildcards)->unless($this->soundsLike, function ($terms) {
            return $terms->map(function ($term) {
                return implode([
                    $this->beginWithWildcard ? '%' : '',
                    $term,
                    $this->endingWithWildcard ? '%' : '',
                ]);
            });
        });

        return $this;
    }

    /**
     * Adds a where clause to the builder, which encapsulates
     * a series 'orWhere' clauses for each column and for
     * each search term.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Zaimea\Searches\SearchThrough      $searchThrough
     * @return void
     */
    public function addSearchQueryToBuilder(EloquentBuilder $builder, SearchThrough $searchThrough): void
    {
        if ($this->termsWithoutWildcards->isEmpty()) {
            return;
        }

        $builder->where(function (EloquentBuilder $query) use ($searchThrough) {
            if (!$searchThrough->isFullTextSearch()) {
                return $searchThrough->getColumns()->each(function ($column) use ($query, $searchThrough) {
                    Str::contains($column, '.')
                        ? $this->addNestedRelationToQuery($query, $column)
                        : $this->addWhereTermsToQuery($query, $searchThrough->qualifyColumn($column));
                });
            }

            $searchThrough
                ->toGroupedCollection()
                ->each(function (SearchThrough $searchThrough) use ($query) {
                    if ($relation = $searchThrough->getFullTextRelation()) {
                        $query->orWhereHas($relation, function ($relationQuery) use ($searchThrough) {
                            $relationQuery->where(function ($query) use ($searchThrough) {
                                $query->orWhereFullText(
                                    $searchThrough->getColumns()->all(),
                                    $this->rawTerms,
                                    $searchThrough->getFullTextOptions()
                                );
                            });
                        });
                    } else {
                        $query->orWhereFullText(
                            $searchThrough->getColumns()->map(fn ($column) => $searchThrough->qualifyColumn($column))->all(),
                            $this->rawTerms,
                            $searchThrough->getFullTextOptions()
                        );
                    }
                });
        });
    }

    /**
     * Adds an 'orWhereHas' clause to the query to search through the given nested relation.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $column
     * @return void
     */
    private function addNestedRelationToQuery(EloquentBuilder $query, string $nestedRelationAndColumn): void
    {
        $segments = explode('.', $nestedRelationAndColumn);

        $column = array_pop($segments);

        $relation = implode('.', $segments);

        $query->orWhereHas($relation, function ($relationQuery) use ($column) {
            $relationQuery->where(
                fn ($query) => $this->addWhereTermsToQuery($query, $query->qualifyColumn($column))
            );
        });
    }

    /**
     * Adds an 'orWhere' clause to search for each term in the given column.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  array|string $columns
     * @return void
     */
    private function addWhereTermsToQuery(EloquentBuilder $query, $column): void
    {
        $column = $this->ignoreCase ? (new MySqlGrammar($query->getConnection()))->wrap($column) : $column;

        $this->terms->each(function ($term) use ($query, $column) {
            $this->ignoreCase
                ? $query->orWhereRaw("LOWER({$column}) {$this->whereOperator} ?", [$term])
                : $query->orWhere($column, $this->whereOperator, $term);
        });
    }

    /**
     * Adds a word count so we can order by relevance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Zaimea\Searches\SearchThrough      $searchThrough
     * @return void
     */
    private function addRelevanceQueryToBuilder($builder, $searchThrough): void
    {
        if (!$this->isOrderingByRelevance() || $this->termsWithoutWildcards->isEmpty()) {
            return;
        }

        if (Str::contains($searchThrough->getColumns()->implode(''), '.')) {
            throw RelevanceException::cannotOrderByRelevance();
        }

        $expressionsAndBindings = $searchThrough->getQualifiedColumns()->flatMap(function ($field) use ($searchThrough) {
            $connection = $searchThrough->getModel()->getConnection();
            $prefix = $connection->getTablePrefix();
            $field = (new MySqlGrammar($connection))->wrap($prefix . $field);

            return $this->termsWithoutWildcards->map(function ($term) use ($field) {
                return [
                    'expression' => "COALESCE(CHAR_LENGTH(LOWER({$field})) - CHAR_LENGTH(REPLACE(LOWER({$field}), ?, ?)), 0)",
                    'bindings'   => [Str::lower($term), Str::substr(Str::lower($term), 1)],
                ];
            });
        });

        $selects  = $expressionsAndBindings->map->expression->implode(' + ');
        $bindings = $expressionsAndBindings->flatMap->bindings->all();

        $builder->selectRaw("{$selects} as terms_count", $bindings);
    }

    /**
     * Builds an array with all qualified columns for
     * both the ids and ordering.
     *
     * @param  \Zaimea\Searches\SearchThrough $currentModel
     * @return array
     */
    protected function makeSelects(SearchThrough $currentModel): array
    {
        return $this->models->flatMap(function (SearchThrough $searchThrough) use ($currentModel) {
            $qualifiedKeyName = $qualifiedOrderByColumnName = $modelOrderKey = 'null';

            if ($searchThrough === $currentModel) {
                $prefix = $searchThrough->getModel()->getConnection()->getTablePrefix();

                $qualifiedKeyName = $prefix . $searchThrough->getQualifiedKeyName();
                $qualifiedOrderByColumnName = $prefix . $searchThrough->getQualifiedOrderByColumnName();

                if ($this->orderByModel) {
                    $modelOrderKey = array_search(
                        get_class($searchThrough->getModel()),
                        $this->orderByModel ?: []
                    );

                    if ($modelOrderKey === false) {
                        $modelOrderKey = count($this->orderByModel);
                    }
                }
            }

            return array_filter([
                DB::raw("{$qualifiedKeyName} as {$searchThrough->getModelKey()}"),
                DB::raw("{$qualifiedOrderByColumnName} as {$searchThrough->getModelKey('order')}"),
                $this->orderByModel ? DB::raw("{$modelOrderKey} as {$searchThrough->getModelKey('model_order')}") : null,
            ]);
        })->all();
    }

    /**
     * Implodes the qualified order keys with a comma and
     * wraps them in a COALESCE method.
     *
     * @return string
     */
    protected function makeOrderBy(): string
    {
        $modelOrderKeys = $this->models->map->getModelKey('order')->implode(',');

        return "COALESCE({$modelOrderKeys})";
    }

    /**
     * Implodes the qualified orderByModel keys with a comma and
     * wraps them in a COALESCE method.
     *
     * @return string
     */
    protected function makeOrderByModel(): string
    {
        $modelOrderKeys = $this->models->map->getModelKey('model_order')->implode(',');

        return "COALESCE({$modelOrderKeys})";
    }

    /**
     * Builds the search queries for each given pending model.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function buildQueries(): Collection
    {
        return $this->models->map(function (SearchThrough $searchThrough) {
            return $searchThrough->getFreshBuilder()
                ->select($this->makeSelects($searchThrough))
                ->tap(function ($builder) use ($searchThrough) {
                    $this->addSearchQueryToBuilder($builder, $searchThrough);
                    $this->addRelevanceQueryToBuilder($builder, $searchThrough);
                });
        });
    }

    /**
     * Returns a bool wether the ordering is set to 'relevance'.
     *
     * @return bool
     */
    private function isOrderingByRelevance(): bool
    {
        return $this->orderBy === 'relevance';
    }

    /**
      * Compiles all queries to one big one which binds everything together
      * using UNION statements.
      *
      * @return
      */
    protected function getCompiledQueryBuilder(): QueryBuilder
    {
        $queries = $this->buildQueries();

        /** @var QueryBuilder $firstQuery */
        $firstQuery = $queries->shift()->toBase();

        $queries->each(fn (EloquentBuilder $query) => $firstQuery->union($query));

        if ($this->orderByModel) {
            $firstQuery->orderBy(
                DB::raw($this->makeOrderByModel()),
                $this->isOrderingByRelevance() ? 'asc' : $this->orderBy
            );
        }

        if ($this->isOrderingByRelevance() && $this->termsWithoutWildcards->isNotEmpty()) {
            return $firstQuery->orderBy('terms_count', 'desc');
        }

        return $firstQuery->orderBy(
            DB::raw($this->makeOrderBy()),
            $this->isOrderingByRelevance() ? 'asc' : $this->orderBy
        );
    }

    /**
     * Paginates the compiled query or fetches all results.
     *
     * @return \Illuminate\Support\Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected function getIdAndOrderAttributes()
    {
        $query = $this->getCompiledQueryBuilder();

        $paginateMethod = $this->simplePaginate ? 'simplePaginate' : 'paginate';

        return $this->pageName
            ? $query->{$paginateMethod}($this->perPage, ['*'], $this->pageName, $this->page)
            : $query->get();
    }

    /**
     * Get the models per type.
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator $results
     * @return \Illuminate\Support\Collection
     */
    protected function getModelsPerType($results): Collection
    {
        return $this->models
            ->keyBy->getModelKey()
            ->map(function (SearchThrough $searchThrough, $key) use ($results) {
                $ids = $results->pluck($key)->filter();

                return $ids->isNotEmpty()
                    ? $searchThrough->getFreshBuilder()->whereKey($ids)->get()->keyBy->getKey()
                    : null;
            });
    }

    /**
     * Retrieve the "count" result of the query.
     *
     * @param  string  $terms
     * @return integer
     */
    public function count(?string $terms = null): int
    {
        $this->initializeTerms($terms ?: '');

        return $this->getCompiledQueryBuilder()->count();
    }

    /**
     * Initialize the search terms, execute the search query and retrieve all
     * models per type. Map the results to a Eloquent collection and set
     * the collection on the paginator (whenever used).
     *
     * @param  string $terms
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Pagination\Paginator
     */
    public function search(?string $terms = null): Collection|LengthAwarePaginator|Paginator
    {
        $this->initializeTerms($terms ?: '');

        $results = $this->getIdAndOrderAttributes();

        $modelsPerType = $this->getModelsPerType($results);

        return $results->map(function ($item) use ($modelsPerType) {
            $modelKey = Collection::make($item)->search(function ($value, $key) {
                return $value && Str::endsWith($key, '_key');
            });

            /** @var Model $model */
            $model = $modelsPerType->get($modelKey)->get($item->$modelKey);

            $model->setAttribute('type', class_basename($model));

            return $model;
        })
            ->pipe(fn (Collection $models) => new EloquentCollection($models))
            ->when($this->pageName, fn (EloquentCollection $models) => $results->setCollection($models));
    }
}
