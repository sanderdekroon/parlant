# Parlant

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Parlant is a PHP library to query posts within WordPress in an expressive way. Get rid of the messy WP_Query array's and start writing expressive queries.

## Install

Via Composer

``` bash
$ composer require sanderdekroon/parlant
```

## Usage

```php
use Sanderdekroon\Parlant\Posttype as Post;

$articles = Post::type('article')->get();
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email sander@dekroon.xyz instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/sanderdekroon/parlant.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/sanderdekroon/parlant/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/sanderdekroon/parlant.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/sanderdekroon/parlant.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/sanderdekroon/parlant.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/sanderdekroon/parlant
[link-travis]: https://travis-ci.org/sanderdekroon/parlant
[link-scrutinizer]: https://scrutinizer-ci.com/g/sanderdekroon/parlant/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/sanderdekroon/parlant
[link-downloads]: https://packagist.org/packages/sanderdekroon/parlant
