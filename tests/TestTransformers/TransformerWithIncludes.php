<?php

declare(strict_types=1);

namespace YucaDoo\PolymorphicFractal\TestTransformers;

/** Transformer with default and available includes used for testing */
class TransformerWithIncludes extends TransformerWithoutIncludes
{
    protected $defaultIncludes = array('defaultInclude', 'otherDefaultInclude');
    protected $availableIncludes = array('availableInclude', 'otherAvailableInclude');

    public function includeDefaultInclude()
    {
        return $this->primitive('defaultIncludeValue');
    }
    public function includeOtherDefaultInclude()
    {
        return $this->primitive('otherDefaultIncludeValue');
    }
    public function includeAvailableInclude()
    {
        return $this->primitive('availableIncludeValue');
    }
    public function includeOtherAvailableInclude()
    {
        return $this->primitive('otherAvailableIncludeValue');
    }
}
