<?php


namespace App\DTO;


class ParentChildDTO {

    /**
     * @var string $type
     */
    private $type = "";

    /**
     * @var string $name
     */
    private $name = "";

    /**
     * @var string $id
     */
    private $id = "";

    /**
     * Info: can be used to build tree of hierarchy
     * @var int $depth
     */
    private $depth = 0;

    /**
     * @var array $children
     */
    private $children = [];

    /**
     * @return string
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void {
        $this->id = $id;
    }

    /**
     * @return array
     */
    public function getChildren(): array {
        return $this->children;
    }

    /**
     * @param array $children
     */
    public function setChildren(array $children): void {
        $this->children = $children;
    }

    /**
     * @param mixed $child
     */
    public function addChild($child){
        $this->children[] = $child;
    }

    /**
     * @return int
     */
    public function getDepth(): int {
        return $this->depth;
    }

    /**
     * @param int $depth
     */
    public function setDepth(int $depth): void {
        $this->depth = $depth;
    }

}