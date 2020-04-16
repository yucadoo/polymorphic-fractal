<?php

declare(strict_types=1);

namespace YucaDoo\PolymorphicFractal\TestTransformers;

use YucaDoo\PolymorphicFractal\Transformer as PolymorphicTransformer;

class PolymorphicTransformerWithCustomRegistryKey extends PolymorphicTransformer
{
    protected function getRegistryKey($data)
    {
        return $data['type'];
    }
}
