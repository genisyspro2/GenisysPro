<?php

declare(strict_types=1);

namespace pocketmine\form;

use Closure;
use pocketmine\Player;
use pocketmine\utils\Utils;
use function is_int;

/**
 * This form type presents a menu to the user with a list of options on it. The user may select an option or close the
 * form by clicking the X in the top left corner.
 */
abstract class MenuForm extends BaseForm {

    /** @var string */
    protected string $content;
    /** @var MenuOption[] */
    private array $options;
    /** @var Closure */
    private Closure $onSubmit;
    /** @var Closure|null */
    private ?Closure $onClose = null;

    /**
     * @param string        $title
     * @param string        $text
     * @param MenuOption[]  $options
     * @param Closure      $onSubmit signature `function(Player $player, int $selectedOption)`
     * @param Closure|null $onClose signature `function(Player $player)`
     */
    public function __construct(string $title, string $text, array $options, Closure $onSubmit, ?Closure $onClose = null) {
        parent::__construct($title);
        $this->content = $text;
        $this->options = array_values($options);
        Utils::validateCallableSignature(function(Player $player, int $selectedOption): void {}, $onSubmit);
        $this->onSubmit = $onSubmit;
        if ($onClose !== null) {
            Utils::validateCallableSignature(function(Player $player): void {}, $onClose);
            $this->onClose = $onClose;
        }
    }

    public function getOption(int $position): ?MenuOption {
        return $this->options[$position] ?? null;
    }

    final public function handleResponse(Player $player, $data): void {
        if ($data === null) {
            if ($this->onClose !== null) {
                ($this->onClose)($player);
            }
        } elseif (is_int($data)) {
            if (!isset($this->options[$data])) {
                throw new FormValidationException("Option $data does not exist");
            }
            ($this->onSubmit)($player, $data);
        } else {
            throw new FormValidationException("Expected int or null, got " . gettype($data));
        }
    }

    protected function getType(): string {
        return "form";
    }

    protected function serializeFormData(): array {
        return [
            "content" => $this->content,
            "buttons" => array_map(function(MenuOption $option): array {
                return $option->jsonSerialize();
            }, $this->options)
        ];
    }
}
