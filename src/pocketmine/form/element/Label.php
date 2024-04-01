<?php

declare(strict_types=1);

namespace pocketmine\form\element;

/**
 * Element which displays some text on a form.
 */
class Label extends CustomFormElement
{
    public function getType(): string
    {
        return "label";
    }

    public function validateValue($value): void
    {
        // Labels do not require validation, as they are non-interactive
    }

    protected function serializeElementData(): array
    {
        return [];
    }
}
