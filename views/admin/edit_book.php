<?php

    use yii\helpers\Html;
    use yii\widgets\ActiveForm;
    use app\models\BookcatalogModel;
    use yii\bootstrap5\Alert;

    $this->title = 'Bookcatalog. Редактирование книги';

    $bookModel = new BookcatalogModel();

    echo $this->render('@app/views/admin/nav');


    echo '
<div class="conteiner">
    <div class="col">
    <h4 class="pt-3 pb-3">Редактирование книги</h4>
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
        'options' => ['data' => ['pjax' => true], 'options' => ['enctype' => 'multipart/form-data']],
    ]);
?>

        <div class="conteiner">

            <div class="col pb-4">
                <?= $form->field($model, 'group_id')->dropdownList( $groupList, [ 'value' => 0 ])->label('Группа каталога'); ?>
            </div>

            <div class="col pb-4">
                <?= $form->field($model, 'title')->textInput()->label('Наименование книги'); ?>
            </div>
            <div class="col pb-4">
                <?= $form->field($model, 'description')->textarea(['rows' => '6'])->label('Описание'); ?>
            </div>
            <div class="col pb-4 col-3">
                <?= $form->field($model, 'year')->textInput(['maxlength' => 50])->label('Год издания'); ?>
            </div>
            <div class="col pb-4">
                <?= $form->field($model, 'author_id')->dropdownList( $authors, 
                [
                    'multiple' => 'true',
                ]
                )->label('Автор'); ?>               
            </div>
            <div class="col pb-4">
                <?= $form->field($model, 'isbn')->textInput()->label('ISBN'); ?>
            </div>

            <div class="col pb-4">
                <?= $form->field($model, 'status')->radioList( 
                    $statusList,
                    [ 'value' => 1 ],
                )->label('Статус группы'); ?>
            </div>

            <div class="col pb-4">
                <label class="control-label">Загрузить изображение</label>
                
                <div>
                    <?= $form->field($model, 'imageFile')->fileInput()->label(false) ?>
                </div>
            </div>

            <div class="col pt-2">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary'])?>
            </div>

        </div>

        <?php ActiveForm::end() ?>

    </div>
</div>

