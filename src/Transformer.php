<?php

declare(strict_types=1);

namespace YucaDoo\PolymorphicFractal;

use Mouf\AliasContainer\AliasContainer;
use League\Fractal\TransformerAbstract;
use League\Fractal\Scope;

class Transformer extends TransformerAbstract
{
    /** @var AliasContainer */
    private $registry;

    /**
     * Tranformer based on current data.
     * @var TransformerAbstract
     */
    private $currentTransformer;

    /**
     * Constructor.
     * @param AliasContainer $registry Registry providing specific transformers.
     */
    public function __construct(AliasContainer $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Getter for registry.
     * @return AliasContainer
     */
    public function getRegistry(): AliasContainer
    {
        return $this->registry;
    }

    /**
     * Getter for currentTransformer.
     * @return TransformerAbstract Current transformer.
     */
    public function getCurrentTransformer()
    {
        return $this->currentTransformer;
    }

    /**
     * Sets currentTransformer based on transformation data.
     * @param mixed $data Transformation data based on which current transformer is set.
     * @return self
     */
    protected function setCurrentTransformer($data)
    {
        // Transformer to be used
        $registryKey = $this->getRegistryKey($data);
        $this->currentTransformer = $this->registry->get($registryKey);
        // Apply current scope to current tranformer as well.
        $this->currentTransformer->setCurrentScope($this->getCurrentScope());
        return $this;
    }

    /**
     * Get registry key from data.
     * @param mixed $data Data for which transformer should be got.
     * @return mixed Registry key for transformer.
     */
    protected function getRegistryKey($data)
    {
        return get_class($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableIncludes()
    {
        return array_merge(
            parent::getAvailableIncludes(),
            $this->getCurrentTransformer()->getAvailableIncludes()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultIncludes()
    {
        return array_merge(
            parent::getDefaultIncludes(),
            $this->getCurrentTransformer()->getDefaultIncludes()
        );
    }

    /**
     *
     */
    public function transform($data)
    {
        $this->setCurrentTransformer($data);
        // The typehint and variable below prevent a PHP Stan linting error.
        /** @var mixed */
        $currentTransformer = $this->getCurrentTransformer();
        return $currentTransformer->transform($data);
    }

    /**
     * {@inheritdoc}
     */
    protected function callIncludeMethod(Scope $scope, $includeName, $data)
    {
        try {
            // Try to invoke include from this transformer
            return parent::callIncludeMethod(...func_get_args());
        } catch (\Throwable $e) {
            // Include method doesn't exist in this class, forward include to current transformer
            return $this->getCurrentTransformer()->callIncludeMethod(...func_get_args());
        }
    }
}
