# Polymorphic Fractal Transformer

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Polymorphic transformer implementation for PHP League's [Fractal package](https://github.com/thephpleague/fractal). Useful for feeds, e.g. notification feed.
The polymorphic transformer can be used as any other transformer and is therefore compatible with packages built on top of Fractal.
This package is compliant with [PSR-1], [PSR-2], [PSR-4] and [PSR-11]. If you notice compliance oversights,
please send a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
[PSR-11]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md

## Install

Via Composer

``` bash
$ composer require yucadoo/polymorphic-fractal
```

## Usage

``` php
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use Mouf\AliasContainer\AliasContainer;
use YucaDoo\PolymorphicFractal\Transformer as PolymorphicTransformer;

// Get heterogeneous data
$objects = array(
    new Like($liker, $post),
    new Comment($commentator, $post),
);

// Create transformer instance, your framework can probably do this automatically.
/** @var Psr\Container\ContainerInterface */
$container;
// Wrap framework specific container with aliasing decorator.
// This allows us to define mapping from item classes to transformer classes.
// We'll call this transformer registry.
$registry = new AliasContainer($container);
$polymorphicTransformer = new PolymorphicTransformer($registry);

// The registry can also be got using the getRegistry() method
$registry = $polymorphicTransformer->getRegistry();
// Configure registry, you'll probably do this in a service provider or in a transformer subclass.
$registry->alias(Like::class, LikeTransformer::class);
$registry->alias(Comment::class, CommentTransformer::class);

// Configure manager and use the transformer as usual
$manager = new League\Fractal\Manager();
// Optionally set serializer
// Optionally parse includes and excludes

$resource = new Collection($objects, $polymorphicTransformer);
$manager->createData($resource)->toArray();
```
As shown in the example above the class of the transformation data is used to get the transformer from the transformer registry. This behvaiour can be modified by overriding the `getRegistryKey()` method.
``` php
use Mouf\AliasContainer\AliasContainer;
use YucaDoo\PolymorphicFractal\Transformer as PolymorphicTransformer;

class NotificationTransformer extends PolymorphicTransformer
{
	public function __construct(AliasContainer $registry)
	{
	    parent::__construct($registry);
	    $registry->alias('like', LikeTransformer::class);
	    $registry->alias('comment', CommentTransformer::class);
    }

	protected function getRegistryKey($data)
	{
	    return $data['type'];
    }
}

$notifications = array(
    array(
        'type' => 'like'
        'liker' => ...,
        ...
    ),
    array(
        'type' => 'comment'
        'commentator' => ...,
        ...
    ),
);
$resource = new Collection($notifications, $notificationTransformer);
```

### Pro tip

To prevent repeated instantiation of the same transformer wrap your framework's IoC cointainer with the `SingletonContainer` decorator provided by the [yucadoo/singleton-container package](link-singleton-container) before passing it into the `AliasContainer`.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email hrcajuka@gmail.com instead of using the issue tracker.

## Credits

- [Hrvoje Jukic][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/yucadoo/polymorphic-fractal.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/yucadoo/polymorphic-fractal/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/yucadoo/polymorphic-fractal.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/yucadoo/polymorphic-fractal.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/yucadoo/polymorphic-fractal.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/yucadoo/polymorphic-fractal
[link-travis]: https://travis-ci.org/yucadoo/polymorphic-fractal
[link-scrutinizer]: https://scrutinizer-ci.com/g/yucadoo/polymorphic-fractal/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/yucadoo/polymorphic-fractal
[link-downloads]: https://packagist.org/packages/yucadoo/polymorphic-fractal
[link-author]: https://github.com/yucadoo
[link-singleton-container]: https://github.com/yucadoo/singleton-container
[link-contributors]: ../../contributors
