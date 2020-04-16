<?php

declare(strict_types=1);

namespace YucaDoo\PolymorphicFractal;

use Mouf\AliasContainer\AliasContainer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use PHPUnit\Framework\TestCase;
use PHPUnit\MockObject\MockObject;
use Psr\Container\ContainerInterface;
use YucaDoo\PolymorphicFractal\TestClasses\ClassA;
use YucaDoo\PolymorphicFractal\TestClasses\ClassB;
use YucaDoo\PolymorphicFractal\TestTransformers\TransformerWithIncludes;
use YucaDoo\PolymorphicFractal\TestTransformers\TransformerWithoutIncludes;

class TransformerTest extends TestCase
{
    /**
     * Evaluated class.
     *
     * @var Transformer
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
        $this->transformer = new Transformer(new AliasContainer($this->containerMock));
    }

    public function testItemWithoutIncludes()
    {
        // Use stdClass as data.
        $data = new \stdClass();
        // Bind transformer in container
        $this->containerMock
            ->method('get')
            ->with('TransformerWithoutIncludes')
            ->willReturn(new TransformerWithoutIncludes());
        // Configure mapping in registry
        $this->transformer->getRegistry()->set('stdClass', 'TransformerWithoutIncludes');
        // Transform data
        $transformationResult = (new Manager())->createData(new Item($data, $this->transformer))->toArray();

        $this->assertEquals(array('data' => array('field' => 'value')), $transformationResult);
    }

    public function testItemWithInclude()
    {
        // Use stdClass as data.
        $data = new \stdClass();
        // Bind transformer in container
        $this->containerMock
            ->method('get')
            ->with('TransformerWithIncludes')
            ->willReturn(new TransformerWithIncludes());
        // Configure mapping in registry
        $this->transformer->getRegistry()->set('stdClass', 'TransformerWithIncludes');
        // Transform data
        $transformationResult = (new Manager())->createData(new Item($data, $this->transformer))->toArray();

        $this->assertEquals(
            array(
                'data' => array(
                    'field' => 'value',
                    'defaultInclude' => 'defaultIncludeValue',
                    'otherDefaultInclude' => 'otherDefaultIncludeValue',
                )
            ),
            $transformationResult
        );
    }

    public function testItemWithExcludedInclude()
    {
        // Use stdClass as data.
        $data = new \stdClass();
        // Bind transformer in container
        $this->containerMock
            ->method('get')
            ->with('TransformerWithIncludes')
            ->willReturn(new TransformerWithIncludes());
        // Configure mapping in registry
        $this->transformer->getRegistry()->set('stdClass', 'TransformerWithIncludes');
        // Transform data
        $manager = new Manager();
        $manager->parseExcludes(array('defaultInclude'));
        $transformationResult = $manager->createData(new Item($data, $this->transformer))->toArray();

        $this->assertEquals(
            array(
                'data' => array(
                    'field' => 'value',
                    'otherDefaultInclude' => 'otherDefaultIncludeValue',
                )
            ),
            $transformationResult
        );
    }

    public function testItemWithIncludedAvailableInclude()
    {
        // Use stdClass as data.
        $data = new \stdClass();
        // Bind transformer in container
        $this->containerMock
            ->method('get')
            ->with('TransformerWithIncludes')
            ->willReturn(new TransformerWithIncludes());
        // Configure mapping in registry
        $this->transformer->getRegistry()->set('stdClass', 'TransformerWithIncludes');
        // Transform data
        $manager = new Manager();
        $manager->parseIncludes(array('availableInclude'));
        $transformationResult = $manager->createData(new Item($data, $this->transformer))->toArray();

        $this->assertEquals(
            array(
                'data' => array(
                    'field' => 'value',
                    'defaultInclude' => 'defaultIncludeValue',
                    'otherDefaultInclude' => 'otherDefaultIncludeValue',
                    'availableInclude' => 'availableIncludeValue',
                )
            ),
            $transformationResult
        );
    }

    public function testSingleTypeCollectionWithoutIncludes()
    {
        // Bind transformers in container
        $this->containerMock
            ->method('get')
            ->willReturnMap(array(
                array('TransformerA', new TransformerWithoutIncludes(array('type' => 'A'))),
                array('TransformerB', new TransformerWithoutIncludes(array('type' => 'B'))),
            ));
        // Configure mapping in registry
        $this->transformer->getRegistry()->set(ClassA::class, 'TransformerA');
        $this->transformer->getRegistry()->set(ClassB::class, 'TransformerB');
        // Transform data
        $data = array(new ClassA(), new ClassA());
        $transformationResult = (new Manager())->createData(new Collection($data, $this->transformer))->toArray();

        $this->assertEquals(
            array(
                'data' => array(
                    array('type' => 'A'),
                    array('type' => 'A'),
                ),
            ),
            $transformationResult
        );
    }

    public function testMixedCollectionWithoutIncludes()
    {
        // Bind transformers in container
        $this->containerMock
            ->method('get')
            ->willReturnMap(array(
                array('TransformerA', new TransformerWithoutIncludes(array('type' => 'A'))),
                array('TransformerB', new TransformerWithoutIncludes(array('type' => 'B'))),
            ));
        // Configure mapping in registry
        $this->transformer->getRegistry()->set(ClassA::class, 'TransformerA');
        $this->transformer->getRegistry()->set(ClassB::class, 'TransformerB');
        // Transform data
        $data = array(new ClassA(), new ClassB(), new ClassB(), new ClassA(), new ClassB());
        $transformationResult = (new Manager())->createData(new Collection($data, $this->transformer))->toArray();

        $this->assertEquals(
            array(
                'data' => array(
                    array('type' => 'A'),
                    array('type' => 'B'),
                    array('type' => 'B'),
                    array('type' => 'A'),
                    array('type' => 'B'),
                ),
            ),
            $transformationResult
        );
    }

    public function testMixedCollectionWithSameInclude()
    {
        // Bind transformers in container
        $transformerA = new TransformerWithIncludes(array('type' => 'A'));
        $transformerA->setDefaultIncludes(array('defaultInclude'));
        $transformerB = new TransformerWithIncludes(array('type' => 'B'));
        $transformerB->setDefaultIncludes(array('defaultInclude'));
        $this->containerMock
            ->method('get')
            ->willReturnMap(array(
                array('TransformerA', $transformerA),
                array('TransformerB', $transformerB),
            ));
        // Configure mapping in registry
        $this->transformer->getRegistry()->set(ClassA::class, 'TransformerA');
        $this->transformer->getRegistry()->set(ClassB::class, 'TransformerB');
        // Transform data
        $data = array(new ClassA(), new ClassB());
        $transformationResult = (new Manager())->createData(new Collection($data, $this->transformer))->toArray();

        $this->assertEquals(
            array(
                'data' => array(
                    array('type' => 'A', 'defaultInclude' => 'defaultIncludeValue'),
                    array('type' => 'B', 'defaultInclude' => 'defaultIncludeValue'),
                ),
            ),
            $transformationResult
        );
    }

    public function testMixedCollectionWithDifferentIncludes()
    {
        // Bind transformers in container
        $transformerA = new TransformerWithIncludes(array('type' => 'A'));
        $transformerA->setDefaultIncludes(array('defaultInclude'));
        $transformerB = new TransformerWithIncludes(array('type' => 'B'));
        $transformerB->setDefaultIncludes(array('otherDefaultInclude'));
        $this->containerMock
            ->method('get')
            ->willReturnMap(array(
                array('TransformerA', $transformerA),
                array('TransformerB', $transformerB),
            ));
        // Configure mapping in registry
        $this->transformer->getRegistry()->set(ClassA::class, 'TransformerA');
        $this->transformer->getRegistry()->set(ClassB::class, 'TransformerB');
        // Transform data
        $data = array(new ClassA(), new ClassB());
        $transformationResult = (new Manager())->createData(new Collection($data, $this->transformer))->toArray();

        $this->assertEquals(
            array(
                'data' => array(
                    array('type' => 'A', 'defaultInclude' => 'defaultIncludeValue'),
                    array('type' => 'B', 'otherDefaultInclude' => 'otherDefaultIncludeValue'),
                ),
            ),
            $transformationResult
        );
    }

    public function testMixedCollectionWithSameAvailableInclude()
    {
        // Bind transformers in container
        $transformerA = new TransformerWithIncludes(array('type' => 'A'));
        $transformerA->setDefaultIncludes(array());
        $transformerA->setAvailableIncludes(array('availableInclude'));
        $transformerB = new TransformerWithIncludes(array('type' => 'B'));
        $transformerB->setDefaultIncludes(array());
        $transformerB->setAvailableIncludes(array('availableInclude'));
        $this->containerMock
            ->method('get')
            ->willReturnMap(array(
                array('TransformerA', $transformerA),
                array('TransformerB', $transformerB),
            ));
        // Configure mapping in registry
        $this->transformer->getRegistry()->set(ClassA::class, 'TransformerA');
        $this->transformer->getRegistry()->set(ClassB::class, 'TransformerB');
        // Transform data
        $data = array(new ClassA(), new ClassB());
        $manager = new Manager();
        $manager->parseIncludes(array('availableInclude'));
        $transformationResult = $manager->createData(new Collection($data, $this->transformer))->toArray();

        $this->assertEquals(
            array(
                'data' => array(
                    array('type' => 'A', 'availableInclude' => 'availableIncludeValue'),
                    array('type' => 'B', 'availableInclude' => 'availableIncludeValue'),
                ),
            ),
            $transformationResult
        );
    }

    public function testMixedCollectionWithDifferentAvailableInclude()
    {
        // Bind transformers in container
        $transformerA = new TransformerWithIncludes(array('type' => 'A'));
        $transformerA->setDefaultIncludes(array());
        $transformerA->setAvailableIncludes(array('availableInclude'));
        $transformerB = new TransformerWithIncludes(array('type' => 'B'));
        $transformerB->setDefaultIncludes(array());
        $transformerB->setAvailableIncludes(array('otherAvailableInclude'));
        $this->containerMock
            ->method('get')
            ->willReturnMap(array(
                array('TransformerA', $transformerA),
                array('TransformerB', $transformerB),
            ));
        // Configure mapping in registry
        $this->transformer->getRegistry()->set(ClassA::class, 'TransformerA');
        $this->transformer->getRegistry()->set(ClassB::class, 'TransformerB');
        // Transform data
        $data = array(new ClassA(), new ClassB());
        $manager = new Manager();
        $manager->parseIncludes(array('availableInclude', 'otherAvailableInclude'));
        $transformationResult = $manager->createData(new Collection($data, $this->transformer))->toArray();

        $this->assertEquals(
            array(
                'data' => array(
                    array('type' => 'A', 'availableInclude' => 'availableIncludeValue'),
                    array('type' => 'B', 'otherAvailableInclude' => 'otherAvailableIncludeValue'),
                ),
            ),
            $transformationResult
        );
    }
}
