<?php

declare(strict_types=1);

namespace Yuca\PolymorphicFractal;

use Mouf\AliasContainer\AliasContainer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use PHPUnit\Framework\TestCase;
use PHPUnit\MockObject\MockObject;
use Psr\Container\ContainerInterface;
use Yuca\PolymorphicFractal\TestTransformers\PolymorphicTransformerWithCustomRegistryKey;
use Yuca\PolymorphicFractal\TestTransformers\TransformerWithoutIncludes;

class CustomRegistryKeyTest extends TestCase
{
    /**
     * Evaluated class.
     *
     * @var PolymorphicTransformerWithCustomRegistryKey
     */
    private $transformer;
    /**
     * IoC container mock.
     *
     * @var ContainerInterface|MockObject
     */
    private $containerMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->transformer = new PolymorphicTransformerWithCustomRegistryKey(new AliasContainer($this->containerMock));
    }

    public function testItem()
    {
        // Use stdClass as data.
        $data = array(
            'type' => 'like',
        );
        // Bind transformer in container
        $this->containerMock
            ->method('get')
            ->with('LikeTransformer')
            ->willReturn(new TransformerWithoutIncludes());
        // Configure mapping in registry
        $this->transformer->getRegistry()->set('like', 'LikeTransformer');
        // Transform data
        $transformationResult = (new Manager())->createData(new Item($data, $this->transformer))->toArray();

        $this->assertEquals(array('data' => array('field' => 'value')), $transformationResult);
    }

    public function testSingleTypeCollection()
    {
        // Bind transformers in container
        $this->containerMock
            ->method('get')
            ->willReturnMap(array(
                array('CommentTransformer', new TransformerWithoutIncludes(array('type' => 'comment'))),
                array('LikeTransformer', new TransformerWithoutIncludes(array('type' => 'like'))),
            ));
        // Configure mapping in registry
        $this->transformer->getRegistry()->set('comment', 'CommentTransformer');
        $this->transformer->getRegistry()->set('like', 'LikeTransformer');
        // Transform data
        $data = array(
            array('type' => 'like'),
            array('type' => 'like'),
        );
        $transformationResult = (new Manager())->createData(new Collection($data, $this->transformer))->toArray();

        $this->assertEquals(
            array(
                'data' => array(
                    array('type' => 'like'),
                    array('type' => 'like'),
                ),
            ),
            $transformationResult
        );
    }

    public function testMixedCollection()
    {
        // Bind transformers in container
        $this->containerMock
            ->method('get')
            ->willReturnMap(array(
                array('CommentTransformer', new TransformerWithoutIncludes(array('type' => 'comment'))),
                array('LikeTransformer', new TransformerWithoutIncludes(array('type' => 'like'))),
            ));
        // Configure mapping in registry
        $this->transformer->getRegistry()->set('comment', 'CommentTransformer');
        $this->transformer->getRegistry()->set('like', 'LikeTransformer');
        // Transform data
        $data = array(
            array('type' => 'comment'),
            array('type' => 'like'),
        );
        $transformationResult = (new Manager())->createData(new Collection($data, $this->transformer))->toArray();

        $this->assertEquals(
            array(
                'data' => array(
                    array('type' => 'comment'),
                    array('type' => 'like'),
                ),
            ),
            $transformationResult
        );
    }
}
