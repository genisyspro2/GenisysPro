<?php

declare(strict_types=1);

namespace pocketmine\form\element;

use InvalidArgumentException;
use pocketmine\form\FormValidationException;

class Slider extends CustomFormElement
{
    /** @var float */
    private float $min;
    /** @var float */
    private float $max;
    /** @var float */
    private float $step;
    /** @var float */
    private float $default;

    public function __construct(string $name, string $text, float $min, float $max, float $step = 1.0, ?float $default = null)
    {
        parent::__construct($name, $text);

        if ($min > $max) {
            throw new InvalidArgumentException("Slider min value should be less than max value");
        }
        $this->min = $min;
        $this->max = $max;

        if ($default !== null) {
            if ($default > $max || $default < $min) {
                throw new InvalidArgumentException("Default must be in range $min ... $max");
            }
            $this->default = $default;
        } else {
            $this->default = $min;
        }

        if ($step <= 0) {
            throw new InvalidArgumentException("Step must be greater than zero");
        }
        $this->step = $step;
    }

    public function getType(): string
    {
        return "slider";
    }

    /**
     * @param float $value
     *
     * @throws FormValidationException
     */
    public function validateValue($value): void
    {
        if (!is_float($value) && !is_int($value)) {
            throw new FormValidationException("Expected float, got " . gettype($value));
        }
        if ($value < $this->min || $value > $this->max) {
            throw new FormValidationException("Value $value is out of bounds (min $this->min, max $this->max)");
        }
    }

    /**
     * @return float
     */
    public function getMin(): float
    {
        return $this->min;
    }

    /**
     * @return float
     */
    public function getMax(): float
    {
        return $this->max;
    }

    /**
     * @return float
     */
    public function getStep(): float
    {
        return $this->step;
    }

    /**
     * @return float
     */
    public function getDefault(): float
    {
        return $this->default;
    }

    protected function serializeElementData(): array
    {
        return [
            "min" => $this->min,
            "max" => $this->max,
            "default" => $this->default,
            "step" => $this->step
        ];
    }
}
