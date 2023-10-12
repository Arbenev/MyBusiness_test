<?php

namespace common\models\Fruit;

use \yii\db\Query;
use \yii\db\ActiveRecord;
use \common\Exception\CannotBeEatenException;
use \common\Exception\CannotFallException;

/**
 * @property int $id Primary
 * @property string $color Apple color
 * @property string $created_at Created at
 * @property string $dropped_at Dropped down at
 * @property int $status Apple status
 * @property int $size What part of apple has been size
 */
class Apple extends ActiveRecord implements IFruit
{

    const COLOR_RED = 'red';
    const COLOR_YELLOW = 'yellow';
    const COLOR_GREEN = 'green';
    const COLOR_WHITE = 'white';
    const STATUS_ON_THE_TREE = 0;
    const STATUS_DROPPED_DOWN = 1;
    const STATUS_GONE_BAD = 2;
    const STATUS_REMOVED = 3;
    const TIME_TO_GO_BAD = 5 * 3600;
    const RANDOM_INTERVAL_TO_CREATE = 6 * 3600;

    private static $statusNames = [
        self::STATUS_ON_THE_TREE => 'On the tree',
        self::STATUS_DROPPED_DOWN => 'Dropped down',
        self::STATUS_GONE_BAD => 'Gone bad',
        self::STATUS_REMOVED => 'Removed',
    ];

    public function __construct($config = [])
    {
        parent::__construct($config);
        if (!isset($config['color'])) {
            $colors = [
                self::COLOR_RED,
                self::COLOR_YELLOW,
                self::COLOR_GREEN,
                self::COLOR_WHITE,
            ];
            $this->color = $colors[random_int(0, 3)];
        }
        if (!isset($config['created_at'])) {
            $this->created_at = date('Y-m-d H:i:s', time() - random_int(0, self::RANDOM_INTERVAL_TO_CREATE));
        }
        if (!isset($config['status'])) {
            $this->status = self::STATUS_ON_THE_TREE;
        }
        if (!isset($config['size'])) {
            $this->size = 100;
        }
    }

    /**
     *
     * @param int $id
     * @return Apple
     */
    public static function findById($id)
    {
        return self::findOne($id);
    }

    public static function findAllExist()
    {
        $query = new Query();
        $query->select('*')->from(self::tableName())->where('status!=' . self::STATUS_REMOVED);
        $apples = [];
        foreach ($query->all() as $appleArray) {
            $apple = self::instantiate($appleArray);
            self::populateRecord($apple, $appleArray);
            $apples[] = $apple;
        }
        return $apples;
    }

    public function eat(int $percent)
    {
        if ($this->status == self::STATUS_ON_THE_TREE) {
            throw new CannotBeEatenException('The apple is on the tree');
        }
        if ($this->status == self::STATUS_GONE_BAD) {
            throw new CannotBeEatenException('The apple has gone bad');
        }
        if ($this->status == self::STATUS_REMOVED) {
            throw new CannotBeEatenException('The apple has been removed');
        }
        if ($percent > $this->size) {
            $percent = $this->size;
        }
        $this->size -= $percent;
        if ($this->size == 0) {
            $this->remove();
        }
        return $this;
    }

    public function fallToGround()
    {
        if ($this->status !== self::STATUS_ON_THE_TREE) {
            throw new CannotFallException('The apple has alredy dropped down');
        }
        $this->status = self::STATUS_DROPPED_DOWN;
        $this->dropped_at = date('Y-m-d H:i:s');
        return $this;
    }

    public function checkTimeToGoBad()
    {
        if (($this->status == self::STATUS_DROPPED_DOWN) && (strtotime($this->dropped_at) < time() - self::TIME_TO_GO_BAD)) {
            $this->status = self::STATUS_GONE_BAD;
            return true;
        } else {
            return false;
        }
    }

    public function remove()
    {
        $this->status = self::STATUS_REMOVED;
        return $this;
    }

    public static function getStatusName($status)
    {
        return self::$statusNames[$status];
    }
}
