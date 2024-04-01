<?php

declare(strict_types=1);

namespace pocketmine\form;

use JsonSerializable;

/**
 * Represents an option on a MenuForm. The option is shown as a button and may optionally have an image next to it.
 */
class MenuOption implements JsonSerializable {
    /**
     * @var string
     */
    private string $text;
    /**
     * @var FormIcon|null
     */
    private ?FormIcon $image;

    public function __construct(string $text, ?FormIcon $image = null) {
        $this->text = $text;
        $this->image = $image;
    }

    public function getText(): string {
        return $this->text;
    }

    public function hasImage(): bool {
        return $this->image !== null;
    }

    public function getImage(): ?FormIcon {
        return $this->image;
    }

    public function jsonSerialize(): array {
        $json = [
            "text" => $this->text
        ];

        if ($this->hasImage()) {
            $json["image"] = $this->image->jsonSerialize();
        }

        return $json;
    }
}
