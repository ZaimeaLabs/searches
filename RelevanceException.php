<?php

declare(strict_types=1);

namespace ZaimeaLabs\Searches;

use Exception;

class RelevanceException extends Exception
{
    public static function cannotOrderByRelevance(): self
    {
        return new self("Order by relevance through nested relations are not possible.");
    }
}
