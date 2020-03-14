<?php

declare(strict_types=1);

namespace Yuca\PolymorphicFractal\TestTransformers;

use Yuca\PolymorphicFractal\Transformer as PolymorphicTransformer;

class PolymorphicTransformerWithCustomRegistryKey extends PolymorphicTransformer
{
    protected function getRegistryKey($data)
    {
        return $data['type'];
    }
}
