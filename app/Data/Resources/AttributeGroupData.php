<?php

namespace App\Data\Resources;

use Lunar\Models\AttributeGroup;
use Spatie\LaravelData\Data;

class AttributeGroupData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $handle
    ) {
    }

    public static function fromModel(AttributeGroup $attrGrp): self
    {

        return new self(
            $attrGrp->getKey(),
            $attrGrp->translate('name'),
            $attrGrp->handle
        );
    }
}
