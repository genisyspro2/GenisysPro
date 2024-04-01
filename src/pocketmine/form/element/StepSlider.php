<?php

declare(strict_types=1);

namespace pocketmine\form\element;

class StepSlider extends BaseSelector
{
    public function getType(): string
    {
        return "step_slider";
    }

    protected function serializeElementData(): array
    {
        return [
            "steps" => array_values($this->options),
            "default" => $this->defaultOptionIndex
        ];
    }
}
