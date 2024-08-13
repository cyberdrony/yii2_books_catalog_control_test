<?php

    use yii\helpers\Html;
    use yii\widgets\ActiveForm;
    use yii\bootstrap5\Alert;

    $this->title = 'Bookcatalog. Создание новой книги';

    echo $this->render('@app/views/admin/nav');

    echo '
<div class="conteiner">
    <div class="col">
';

    if(empty($edit_data)) {

        echo '<h4 class="pt-3 pb-3">Создание новой книги</h4>';
    }
    else {
        echo '<h4 class="pt-3 pb-3">Редактирование книги</h4>';
    }

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
                <?= $form->field($model, 'group_id')->dropdownList( $groupList, [ 'value' => $edit_data[0]['group_id'] ?? 0 ])->label('Группа каталога'); ?>
            </div>

            <div class="col pb-4">
                <?= $form->field($model, 'title')->textInput([ 'value' => $edit_data[0]['title'] ?? '' ])->label('Наименование книги'); ?>
            </div>
            <div class="col pb-4">
                <?= $form->field($model, 'description')->textarea(['rows' => '6', 'value' => $edit_data[0]['description'] ?? '' ])->label('Описание'); ?>
            </div>
            <div class="col pb-4 col-3">
                <?= $form->field($model, 'year')->textInput(['maxlength' => 50, 'value' => $edit_data[0]['year'] ?? ''])->label('Год издания'); ?>
            </div>
            <div class="col pb-4">
                <?= $form->field($model, 'author_id')->dropdownList( $authors, [ 'multiple' => 'true', 'value' => $edit_data[0]['author_id_list'] ?? []]
                )->label('Автор'); ?>               
            </div>
            <div class="col pb-4">
                <div class="conteiner">

                <div class="control-label">Новый автор</div>

                <div class="row">
                    <div class="col-4"><?= $form->field($model, 'new_author_lname')->textInput( ['placeholder' => "Фамилия"] )->label(false); ?></div>
                    <div class="col-4"><?= $form->field($model, 'new_author_fname')->textInput( ['placeholder' => "Имя"] )->label(false); ?></div>
                    <div class="col-4"><?= $form->field($model, 'new_author_sname')->textInput( ['placeholder' => "Отчество"] )->label(false); ?></div>
                </div>
                </div>
                               
            </div>
            <div class="col pb-4">
                <?= $form->field($model, 'isbn')->textInput([ 'value' => $edit_data[0]['isbn'] ?? '' ])->label('ISBN'); ?>
            </div>

            <div class="col pb-4">
                <?= $form->field($model, 'status')->radioList( 
                    $statusList,
                    [ 'value' => $edit_data[0]['status'] ?? 1 ],
                )->label('Статус группы'); ?>
            </div>

            <div class="col pb-4">
                <div class="control-label">Загрузить изображение</div>
                
<?php
            if( isset($edit_data[0]['image']) && !empty($edit_data[0]['image']) ) {
                echo '<br><img src="'.Yii::getAlias('@web').'/images/books/'.$edit_data[0]['image'].'" style="width:100px; height:auto"><br><br>';
                echo $form->field($model, 'image_exists')->hiddenInput([ 'value' => $edit_data[0]['image'] ?? '' ])->label(false);
            }

?>

                <div>
                    <?= $form->field($model, 'imageFile')->fileInput()->label(false) ?>
                </div>
            </div>

            <div class="col pt-2">

                    <?php

                    if(empty($edit_data)) {
                        echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary']);
                    }
                    else {
                        echo Html::submitButton('Изменить', ['class' => 'btn btn-primary', 'name' => 'change_book', 'value' => '1' ]).'&nbsp;&nbsp;&nbsp;';
                        echo Html::submitButton('Удалить', ['class' => 'btn btn-danger', 'name' => 'delete_book', 'id' => 'delete_booc', 'value' => '1',
                            'data' => [ 'confirm' => 'Вы уверены что хотите удалить книгу?' ],
                        ]);
                    }
                ?>

            </div>

        </div>

        <?php ActiveForm::end() ?>

    </div>
</div>

