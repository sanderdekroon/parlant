# Parlant

Parlant is a PHP library to query posts within WordPress in an expressive way. Get rid of the messy WP_Query array's and start writing expressive queries.

Remember this?
```php
$args = array(
  'post_status' => 'future',
  'meta_query' => array(
     array(
        'key' => '_thumbnail_id',
        'value' => '',
        'compare' => '!='
     )
  )
);
$slider_posts = new WP_Query($args);
```
What if you could simplify it to this?
```php
$slider_posts = Post::type('post')
    ->where('post_status', 'future')
    ->whereMeta('_thumbnail_id', '!=', '')
    ->get();
```

Parlant is a Query Builder, which means you can build queries in a simple and expressive way. It still uses WP_Query under the hood, but it takes away the pain and clutter of argument building.

## Usage

### Basic usage
Parlant's syntax is heavily inspired by [Laravel's Query Builder](https://github.com/illuminate/database) package. 

To start building queries, simply start by calling the static method `type()` on the Parlant class. Either pass in the posttype you're querying into the `type()` method or call `all()` to query all posttypes.

These methods return a `PosttypeBuilder` instance on which you can chain multiple methods. End a query by calling `get()` to get all results of the query.

Although Parlant uses WP_Query in the background, it overrides some of it's default settings. Parlant will, by default, return all found posts (`'posts_per_page' => -1`, remember?) instead of the default. 
This behavior can be overwritten by changing the settings of Parlant **(@todo ADD LINK)**

For example: get all posts of posttype 'article':
```php
use Sanderdekroon\Parlant\Posttype as Post;

$articles = Post::type('article')->get();
```
The variable `$articles` will now contain an array of WP_Post instances. Alternatively, it's possible to configure Parlant to return a WP_Query instance, an array of query arguments or inject your own output formatter. **(@todo ADD LINK)**

### Limiting results
Besides changing Parlant's configuration, it's also possible to pass any limiting methods to the Query Builder. Instead of calling `get()` to return results, you can call `first()` to return the first result:
```php
$article = Post::type('article')->first();
```
Depending on your configuration, this returns a single WP_Post instance. Remember, you can define your own output settings.

When you're needing a specific amount of posts to be returned, add the `limit()` method to the query and end it with `get()`:
```php
$fiveArticles = Post::type('article')->limit(5)->get();
```

### Specify results
Use the `where()` method when specifying results. Basically, all the arguments used for WP_Query in the outer array are supported through this method. 

For example, you can specify the author by calling `where('author', 7)` on the PosttypeBuilder. Want to get all posts in 1970? Go right ahead: `where('year', 1970)`.

More than 50 arguments are supported through the where method. For a lot of them Parlant has built in helpers, to make querying easier and better looking. For a full list of supported methods in Parlant ... **@todo ADD LINK TO TECHNICAL DOCS**

**@todo add negating parameters **
**@todo implement a dictionary? E.g. ID != 13 would result in posts_not_in = 13**

A simple query with `where()` would look like this:

```php
use Sanderdekroon\Parlant\Posttype as Post;

// Get all articles that are within the category called 'linux', limit to 5 results.
Post::type('articles')
    ->where('category_name', 'linux')
    ->limit(5)
    ->get();
```

It's possible to add multiple where statements, apply a limiting method and order the result. All in one chain.
```php  
// Get articles written by author 42, within the category called 'Meaning of Life' 
// and limit it to 14 results.
Post::type('articles')
    ->where('author', '42')
    ->where('category_name', 'Meaning of Life')
    ->limit(14)
    ->get();
```

### Meta query
Of course you can also utilize the meta_query of the WP_Query class. Just add the `whereMeta()` method. This means you will be querying for certain post_meta key/value combination. For example:
```php
// Get all posts within the posttype 'post' where the post_meta key 'foo' should be equal to 'bar'.
Post::type('post')->whereMeta('foo', '=', 'bar')->get();
```

#### Operators
You can pass in any of the supported operators. If no operator is supplied, Parlant defaults to '='.
```php
// Get all posts within the 'post' posttype where the post_meta key 'secretkey' is not equal to 'hunter2'.
Post::type('post')->whereMeta('secretkey', '!=', 'hunter2')->get();
```
Since Parlant is utilizing the WP_Query class, you can pass in any of the supported operators of WP_Query. For reference, the operators are:
```
=, !=, >, >=, <, <=, LIKE, NOT LIKE, IN, NOT IN,
BETWEEN, NOT BETWEEN, NOT EXISTS, REGEXP, NOT REGEXP, RLIKE
```

#### Meta type
You can also specify the meta type to query for. If you're supplying a meta type, you have to supply an operator. The default meta type is 'CHAR' which should suffice in the most (basic) situations.
```php
Post::type('shoes')->whereMeta('size', '=', 37, 'NUMERIC')->get();
```
Just like with the operators, you can pass in any of the supported meta types of WP_Query. For reference, the types are:
```
NUMERIC, BINARY, CHAR, DATE, DATETIME,
DECIMAL, SIGNED, TIME, UNSIGNED
```
Just like with the WP_Query class, it's possible to specify the precision or scale for the DECIMAL and NUMERIC TYPES. For example 'DECIMAL(10,5)' or 'NUMERIC(10)' are valid.
#### Meta relation
It's possible to define the relation between multiple meta queries. Chaining multiple `whereMeta()` methods will create an 'AND' relation by default. Use `orWhereMeta()` to set the relation to 'OR'.

For example: we want to query all posts within the posttype 'shirts' but only want the posts where the size is either 'M' or 'L'. 
```php
Post::type('shirts')
    ->whereMeta('size', 'M')
    ->orWhereMeta('size', 'L')
    ->get();
```

To construct more advanced meta queries, use a closure to start a nested meta query. 
Continuing from the example above, we want to get all shirts where the size is either 'M' or 'L' and where the color is red.

```php
Post::type('shirts')
    ->whereMeta(function() {
        return $this->relation('OR')->where('size', 'M')->where('size', 'L');
    })
    ->whereMeta('color', 'red')
    ->get();
```
When using a closure, use the `relation()` method to specify the relation between the queries. The default is AND.
> **Note:** Parlant knows that the closure is within the `whereMeta()` method, therefor it's not needed to call `whereMeta()` within the closure. Instead, you can just call the `where()` method. 

> **Note:** Just like with a normal `whereMeta()` method, you can still pass in different operators and meta types. 

#### Nesting madness
Parlant will resolve all nested queries recursively, so there's no hard limit on the level of queries. 
```php
Post::type('*')->whereMeta(function() {
        return $this->relation('OR')
            ->where('size', 'L')
            ->where('size', 'M')
            ->where(function() {
                return $this->relation('AND')
                    ->where('color', 'RED')
                    ->where('fit', 'slim')
                    ->where(function() {
                        return $this->where('foo', 'bar')
                        ->where('bar', 'baz');
                    });
            });
    })->get()

```
Although it's possible to nest the queries quite deep, I would not recommend to go deeper than 3 levels. 

*Just for your own sanity.*

### Taxonomy queries / Term queries
Besides custom post meta, it's also possible to query custom taxonomies. Like the meta query, start a taxonomy/term query by using the `whereTerm()` method. Start by passing in the taxonomy, then the term field, the operator (optional) and finally the term value.
```php
// Get all posts within the posttype 'jeans' that are within the term called '37' of the 'size' taxonomy.
Post::type('jeans')->whereTerm('size', 'name', 'IN', 37)->get();
```

Of course you can pass in any of the other tax query operators. Omitting the operator will make Parlant fallback to the default 'IN' operator.
> **Note:** the tax query has different operators than the meta query. The operators are IN, NOT IN, AND, EXISTS, NOT EXISTS.


#### Taxonomy/Term relation
The relationship handling between multiple taxonomy queries is handled the same way as a meta query. Chaining multiple `whereTerm()` methods will create an 'AND' relation by default. Use `orWhereTerm()` to set the relation to 'OR'.

For example: we want to query all posts within the posttype 'jeans' but only want the posts where the size is either '32' or '33'. In this example, size is the taxonomy.
```php
Post::type('jeans')
    ->whereTerm('size', 'name', '32')
    ->orWhereTerm('size', 'name', '33')
    ->get();
```

The taxonomy/term query also supports nested queries. There's one difference: there's no need to specify which field you are querying as there are different methods taking care of that.
When you're within a closure constructing a nested query, simply call the field you want to query as a method. E.g. `slug()`, `name()`, `id()` or `termTaxonomyId()`.

Continuing from the example above, we want to get all jeans where the size is either '32' or '33' and where the color is 'blue'.

```php
Post::type('jeans')
    ->whereTerm(function() {
        return $this->relation('OR')
            ->name('size', '32')
            ->name('size', '33');
    })
    ->whereTerm('color', 'term_slug', 'blue')
    ->get();
```

It's possible to mix the different methods when you are within a nested taxonomy query.

#### Setting a default taxonomy
When you are querying a lot of values within a single taxonomy, it's possible to set a default taxonomy for a nested taxonomy query. Instead of immediately starting a closure, pass in the taxonomy name first and then a closure:

```php
// Query all jeans that are either in the term 32, 33, 34 or 35, within the 'size' taxonomy.
Post::type('jeans')
    ->whereTaxonomy('size', function () {
        return $this->relation('OR')->name('32')->name('33')->name('34')->name('35');
    })->get();

```





## Configuration
The default configuration of Parlant can be changed at any time, but it's recommended to configure it as early as possible to avoid unexpected results. Changes are made by calling the `configure()` method on an instance of Parlant. 

These settings are set globally, so there's no need to change the configuration every time you're starting a query.

### Changing output
Parlant uses Output Formatters to determine how to output the query results. By default it will return an array of WP_Post instances. If you want to start a WP_Query loop, simply change the default settings of Parlant:
```php
use Sanderdekroon\Parlant\Posttype as Post;

$posttypes = new Post;
$posttypes->configure(
    new PosttypeConfigurator([
        'return'    => 'query',
    ])
);
```
Parlant has three built in output formatters:

 - `array`, which will output an array of WP_Post instances
 - `argument`, which will output the raw query arguments
 - `query`, which will output a WP_Query instance
 
Alternatively, you can supply your own output formatter by supplying the fully namespaced classname. Remember that it should adhere to the `FormatterInterface` interface. **(@todo ADD LINK + EXPLANATION)**

## Methods

### Returns results


`get()` - Returns all results

`first()` - Returns only the first result.

`all()` - Same as get, but ignores the `limit()` method or any other default setting.

`count()` - Returns an integer representing the count of found posts.

`find($id)` - Returns a single result (if any) where the ID is equal to the integer passed in.

`pluck($column_name)` - Only returns an array of the specified column name.


`avg('column_name')` - Will trigger a BadMethodCallException as this is unimplemented.

`max('column_name')` - Will trigger a BadMethodCallException as this is unimplemented.

`min('column_name')` - Will trigger a BadMethodCallException as this is unimplemented.



### Limiting methods

`limit($number)` - Limits the output to the number passed in.

`select('column_name')` - Will trigger a BadMethodCallException as this is unimplemented.


### Where

`where($column, $value)` - Specifies the result.

`whereMeta()` - Specify the result by searching for a certain meta key/value relation.

`whereTaxonomy()` - Specify the result by searching for a certain taxonomy/term relation.


`whereBetween() / whereNotBetween()` - Will trigger a BadMethodCallException as this is unimplemented.

`whereIn() / whereNotIn()` - Will trigger a BadMethodCallException as this is unimplemented.


### Ordering

`orderBy($column, $direction = 'ASC')` - Will trigger a BadMethodCallException as this is unimplemented.

`groupBy()` - Will trigger a BadMethodCallException as this is unimplemented.

`inRandomOrder()` - Will trigger a BadMethodCallException as this is unimplemented.
