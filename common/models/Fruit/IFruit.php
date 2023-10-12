<?php

namespace common\models\Fruit;

interface IFruit
{

    public function fallToGround();

    public function eat(int $percent);

    public function remove();
}
