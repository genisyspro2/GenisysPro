<?php

declare(strict_types=1);

namespace pocketmine\form\element;

use pocketmine\form\FormValidationException;
use function is_bool;

/**
 * Represents a UI on/off switch. The switch may have a default value.
 */
class Toggle extends CustomFormElement
{
    /** @var bool */
    private bool $default;

    public function __construct(string $name, string $text, bool $defaultValue = false)
    {
        parent::__construct($name, $text);
        $this->default = $defaultValue;
    }

    public function getType(): string
    {
        return "toggle";
    }

    /**
     * @return bool
     */
    public function getDefaultValue(): bool
    {
        return $this->default;
    }

    /**
     * @param bool $value
     *
     * @throws FormValidationException
     */
    public function validateValue($value): void
    {
        if (!is_bool($value)) {
            throw new FormValidationException("Expected bool, got " . gettype($value));
        }
    }

    protected function serializeElementData(): array
    {
        return [
            "default" => $this->default
        ];
    }
}
