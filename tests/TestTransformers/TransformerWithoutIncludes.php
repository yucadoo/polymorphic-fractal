<?php

declare(strict_types=1);

namespace YucaDoo\PolymorphicFractal\TestTransformers;

use League\Fractal\TransformerAbstract;

/** Simple transformer used for testing */
class TransformerWithoutIncludes extends TransformerAbstract
{
    /** @var array */
    private $result;

    /**
     * Constructor
     * @param array $result Transformation result.
     */
    public function __construct(array $result = null)
    {
        // Default result if not specified
        $this->result = $result ?? array('field' => 'value');
    }

    public function transform($data)
    {
        return $this->result;
    }
}
