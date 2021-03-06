# Upgrading

Because there are many breaking changes an upgrade is not that easy. There are many edge cases this guide does not cover. We accept PRs to improve this guide.

## From v1 to v2

There are a lot of renamed methods and classes in this release. An advanced IDE like PhpStorm is recommended to rename these methods and classes in your code base. Use the refactor -> rename functionality instead of find & replace.

- rename `Spatie\QueryBuilder\Sort` to `Spatie\QueryBuilder\AllowedSort`
- rename `Spatie\QueryBuilder\Included` to `Spatie\QueryBuilder\AllowedInclude`
- rename `Spatie\QueryBuilder\Filter` to `Spatie\QueryBuilder\AllowedFilter`
- replace request macro's like `request()->filters()`, `request()->includes()`, etc... with their related methods on the `QueryBuilderRequest` class that can be resolved from the container:
    * `request()->includes()` -> `app(QueryBuilderRequest::class)->includes()`
    * `request()->filters()` -> `app(QueryBuilderRequest::class)->filters()`
    * `request()->sorts()` -> `app(QueryBuilderRequest::class)->sorts()`
    * `request()->fields()` -> `app(QueryBuilderRequest::class)->fields()`
    * `request()->appends()` -> `app(QueryBuilderRequest::class)->appends()`
- make sure the second argument for `AllowedSort::custom()` is an instance of a sort class, not a classname
    * `AllowedSort::custom('name', MySort::class)` -> `AllowedSort::custom('name', new MySort())`
- make sure the second argument for `AllowedFilter::custom()` is an instance of a filter class, not a classname
    * `AllowedFilter::custom('name', MyFilter::class)` -> `AllowedFilter::custom('name', new MyFilter())`
- make sure all required sorts are allowed using `allowedSorts()`
- make sure all required field selects are allowed using `allowedFields()`
- make sure `allowedFields()` is always called before `allowedIncludes()`
