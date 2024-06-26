<p align="center">
  <a href="https://zaimea.com/" target="_blank">
    <img src=".github/searches.svg" alt="Searches" width="300">
  </a>
</p>
<p align="center">
  Generate pdf in your application.
<p>
<p align="center">
    <a href="https://github.com/zaimealabs/searches/actions/workflows/searches-tests.yml"><img src="https://github.com/zaimealabs/searches/actions/workflows/searches-tests.yml/badge.svg" alt="Searches Tests"></a>
    <a href="https://github.com/zaimealabs/searches/blob/main/LICENSE"><img src="https://img.shields.io/badge/License-Mit-brightgreen.svg" alt="License"></a>
</p>
<div align="center">
  Hey 👋 thanks for considering making a donation, with these donations I can continue working to contribute to ZaimeaLabs projects.
  
  [![Donate](https://img.shields.io/badge/Via_PayPal-blue)](https://www.paypal.com/donate/?hosted_button_id=V6YPST5PUAUKS)
</div>

## Usage
```php
    use ZaimeaLabs\Searches\Search;

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
    use ZaimeaLabs\Searches\Search;

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
