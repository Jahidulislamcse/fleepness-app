<?php

namespace App\Data\Resources;

use Illuminate\Support\Collection;
use Lunar\Models\Attribute;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class AttributeData extends Data
{
    public function __construct(
        public int $id,
        public AttributeGroupData $attributeGroup,
        public int $position,
        public string $name,
        public string $handle,
        public string $section,
        public string $type,
        public bool $required,
        public array $configuration,
        public bool $system,
    ) {
    }

    public static function fromModel(Attribute $attribute): self
    {
        /**
         * @var Collection<string, string>
         */
        $conf = $attribute->configuration;

        return new self(
            $attribute->getKey(),
            AttributeGroupData::from($attribute->attributeGroup),
            $attribute->position,
            $attribute->translate('name'),
            $attribute->handle,
            $attribute->section,
            $attribute->type,
            $attribute->required,
            $conf->toArray(),
            $attribute->system
        );
    }
}
