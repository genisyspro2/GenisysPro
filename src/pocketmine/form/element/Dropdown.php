<?php

declare(strict_types=1);

namespace pocketmine\form\element;

class Dropdown extends BaseSelector
{
    public function getType(): string
    {
        return "dropdown";
    }

    protected function serializeElementData(): array
    {
        return [
            "options" => array_values($this->options),
            "default" => $this->defaultOptionIndex
        ];
    }
}
