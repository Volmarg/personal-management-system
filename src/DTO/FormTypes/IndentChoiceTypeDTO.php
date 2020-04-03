<?php
namespace App\DTO\FormTypes;


class IndentChoiceTypeDTO {

    const KEY_DEPTH_LEVEL = "depthLevel";
    const KEY_VALUE       = "value";

    /**
     * @var int
     */
    private $depth_level = null;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var string $key
     */
    private $key = '';

    /**
     * @return int
     */
    public function getDepthLevel(): int {
        return $this->depth_level;
    }

    /**
     * @param int $depth_level
     */
    public function setDepthLevel(int $depth_level): void {
        $this->depth_level = $depth_level;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getKey(): string {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key): void {
        $this->key = $key;
    }

}