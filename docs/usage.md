---
title: How to use package
description: How to use package
github: https://github.com/zaimealabs/searches/edit/main/
---

# Searches Usage

[[TOC]]

## Usage

```php
    use Zaimea\Searches\Search;

    $results = Search::in(User::class, 'name')
        ->search('Custura');
```

Use `->when()`
```php
    Search::new()
        ->when($user->isAdmin(), fn($search) => $search->in(User::class, 'name'))
        ->search('Custura');
```

Multiple column
```php
    Search::in(User::class, ['name', 'username'])
        ->search('Custura');
```

Search through relationships
```php
    Search::in(User::class, ['posts.title'])
        ->search('laravel');
```

Eager load relationships
```php
    Search::in(Post::with('comments'), 'title')
        ->in(Video::with('likes'), 'title')
        ->search('laravel');
```

Multi-words 
```php 
    use Zaimea\Searches\Search;

    Search::in(Blog::class, 'title')
        ->in(Video::class, 'title')
        ->search('"laravel livewire alpine"');
```

Sorting
```php
    ->orderByDesc()
```
```php
    ->orderByRelevance()
```
```php
    ->orderByModel([Post::class, Video::class,])
```

Paginate
```php
    ->paginate()
    
    ->paginate($perPage = 10, $pageName = 'page', $page = 1)
    # OR
    ->simplePaginate()
    
    ->simplePaginate($perPage = 10, $pageName = 'page', $page = 1)
```
