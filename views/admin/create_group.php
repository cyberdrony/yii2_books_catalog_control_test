<?php

    use yii\helpers\Html;
    use yii\widgets\ActiveForm;
    use app\models\BookcatalogModel;
    use yii\bootstrap5\Alert;

    $this->title = 'Bookcatalog. Создание новой группы книг';

    $bookModel = new BookcatalogModel();

    echo $this->render('@app/views/admin/nav');


    echo '
<div class="conteiner">
    <div class="col">
    <h4 class="pt-3 pb-3">Создание новой группы книг</h4>
    ';

    if( isset($save_result) ) {

        if( !empty($save_result) ) {

            echo Alert::widget([
                'options' => [
                    'class' => 'save_info_block alert-success'
                ],
                'body' => 'Данные успешно сохранены'
            ]);
        }
    }

    $form = ActiveForm::begin([
        'options' => ['data' => ['pjax' => true]],
    ]);
?>

        <div class="conteiner">

            <div class="col pb-3">
                <?= $form->field($model, 'name')->textInput()->label('Наименование группы'); ?>
            </div>
            <div class="col pb-3">
                <?= $form->field($model, 'parent_group_id')->dropdownList( $groupList )->label('Родительская группа'); ?>
            </div>
            <div class="col pb-3">
                <?= $form->field($model, 'status')->radioList( 
                    $statusList,
                    [ 'value' => 1 ],
                )->label('Статус группы'); ?>
            </div>
            <div class=\"col\">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary'])?>
            </div>

        </div>

        <?php ActiveForm::end() ?>

    </div>
</div>

