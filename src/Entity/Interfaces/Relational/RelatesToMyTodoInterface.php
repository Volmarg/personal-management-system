<?php

namespace App\Entity\Interfaces\Relational;

use App\Entity\Modules\Todo\MyTodo;

interface RelatesToMyTodoInterface {

    public function getTodo(): ?MyTodo;

    public function setTodo(?MyTodo $todo): self;
}