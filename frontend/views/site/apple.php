<?php

use yii\helpers\Html;
use common\models\Fruit\Apple;

/**
 * @var $apples Apple[]
 */
echo Html::button('Generate apples', ['class' => 'btn btn-primary', 'onclick' => 'generate()']);
echo Html::button('An hour has passed', ['class' => 'btn btn-info', 'onclick' => 'hourPassed()']);
?>
<table id="main">
    <thead>
        <tr>
            <th>ID</th>
            <th>Color</th>
            <th>Created at</th>
            <th>Status</th>
            <th>Fell at</th>
            <th>Size</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        /** @var $apple Apple */
        foreach ($apples as $apple) {
            ?>
            <tr>
                <td class="apple_id"><?= $apple->id ?><input type="hidden" name="id" value="<?= $apple->id ?>"></td>
                <td class="<?= $apple->color ?>"><?= $apple->color ?></td>
                <td><?= $apple->created_at ?></td>
                <td><?= Apple::getStatusName($apple->status) ?></td>
                <td><?= $apple->dropped_at ?></td>
                <td class="size"><?= $apple->size ?></td>
                <td>
                    <?php
                    switch ($apple->status) {
                        case Apple::STATUS_ON_THE_TREE:
                            echo Html::button('Drop the apple', ['class' => 'btn btn-primary', 'onclick' => 'drop(this)']);
                            break;
                        case Apple::STATUS_DROPPED_DOWN:
                            echo Html::textInput('eat', 10, ['class' => 'eat', 'onblur' => 'checkPercent(this)']);
                            echo Html::button('Eat the apple', ['class' => 'btn btn-primary', 'onclick' => 'eat(this)']);
                            break;
                        case Apple::STATUS_GONE_BAD:
                            echo Html::button('Remove', ['class' => 'btn btn-primary', 'onclick' => 'remove(this)']);
                            break;
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>
<script>
    let maxNumberofApplesToGenerate = 5;
    function getRandomInt(max) {
        return Math.floor(Math.random() * max + 1);
    }

    function makeRow(apple) {
        let buttons = '';
        if (apple.status === <?= Apple::STATUS_ON_THE_TREE ?>) {
            buttons = '<?php
        echo Html::button('Drop the apple', ['class' => 'btn btn-primary', 'onclick' => 'drop(this)']);
        ?>';
        } else if (apple.status === <?= Apple::STATUS_DROPPED_DOWN ?>) {
            buttons = '<?php
        echo Html::textInput('eat', 10, ['class' => 'eat', 'onblur' => 'checkPercent(this)']);
        echo Html::button('Eat the apple', ['class' => 'btn btn-primary', 'onclick' => 'eat(this)']);
        ?>';
        } else if (apple.status === <?= Apple::STATUS_GONE_BAD ?>) {
            buttons = '<?php
        echo Html::button('Remove', ['class' => 'btn btn-primary', 'onclick' => 'remove(this)']);
        ?>';
        } else if (apple.status === <?= Apple::STATUS_REMOVED ?>) {
            buttons = '<h5>Removing...</h5>';
        }
        let tr = '<td class="apple_id">' + apple.id + '<input type="hidden" name="id" value="' + apple.id + '"></td>' +
                '<td class="' + apple.color + '">' + apple.color + '</td>' +
                '<td>' + apple.created_at + '</td>' +
                '<td>' + apple.statusName + '</td>' +
                '<td>' + (apple.dropped_at ? apple.dropped_at : '') + '</td>' +
                '<td class="size">' + apple.size + '</td>' +
                '<td>' + buttons + '</td>';
        return tr;
    }

    function changeRow(apple, tr) {
        let content = makeRow(apple);
        $(tr).empty().append(content);
    }

    function generate() {
        let number = getRandomInt(maxNumberofApplesToGenerate);
        for (let i = 1; i <= number; i++) {
            $.get(
                '<?= Yii::$app->urlManager->createUrl('/apple/generate') ?>',
                    null,
                    function (apple) {
                        $('#main tbody').append('<tr>' + makeRow(apple) + '</tr>');
                    },
                    'json'
                    );
        }
    }

    function drop(btn) {
        let tr = $(btn).closest('tr');
        let id = tr.find('input[name=id]').val();
        $.post(
                '<?= Yii::$app->urlManager->createUrl('/apple/drop') ?>',
                {id: id},
                function (apple) {
                    if (apple.error) {
                        alert(apple.error);
                    } else {
                        changeRow(apple, tr);
                    }
                },
                'json'
                );
    }

    function eat(btn) {
        let tr = $(btn).closest('tr');
        let id = tr.find('input[name=id]').val();
        let percents = $(btn).closest('td').find('input.eat').val();
        $.post(
                '<?= Yii::$app->urlManager->createUrl('/apple/eat') ?>',
                {
                    id: id,
                    percents: percents
                },
                function (apple) {
                    if (apple.error) {
                        alert(apple.error);
                    } else {
                        changeRow(apple, tr);
                        if (apple.status === <?= Apple::STATUS_REMOVED ?>) {
                            setTimeout(function () {
                                tr.remove();
                            }, 1000);
                        }
                    }
                },
                'json'
                );
    }

    function checkPercent(input) {
        let percent = parseInt($(input).val());
        if (isNaN(percent) || (percent < 0)) {
            $(input).val(0);
        } else {
            let size = parseInt($(input).closest('tr').find('td.size').text());
            if (percent > size) {
                $(input).val(size);
            }
        }
    }

    function remove(btn) {
        let tr = $(btn).closest('tr');
        let id = tr.find('input[name=id]').val();
        $.post(
                '<?= Yii::$app->urlManager->createUrl('/apple/remove') ?>',
                {id: id},
                function (data) {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        tr.remove();
                    }
                },
                'json'
                );
    }

    function hourPassed() {
        $.post(
                '<?= Yii::$app->urlManager->createUrl('/apple/hour') ?>',
                {},
                function () {
                    window.location.reload();
                }
        );
    }
</script>