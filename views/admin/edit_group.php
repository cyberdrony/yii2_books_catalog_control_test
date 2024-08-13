<?php

    use yii\helpers\Html;
    use yii\widgets\ActiveForm;
    use app\models\BookcatalogModel;
    use yii\bootstrap5\Alert;

    $this->title = 'Bookcatalog. Редактирование группы книг';

    $bookModel = new BookcatalogModel();

    echo $this->render('@app/views/admin/nav');

    $editedData = [
        'name'              => '',
        'group_id'   => 0,
        'parent_group_id'   => 0,
        'status'            => 1
    ];

    if( isset($edited_data[0]) && is_array($edited_data[0]) && !empty($edited_data[0]) ) {
        $editedData = $edited_data[0];
    }
    
    echo '
    <div class="conteiner">
    <div class="col">
    <h4 class="pt-3 pb-3">Редактирование группы книг</h4>
    ';

    $form = ActiveForm::begin([
        //'options' => ['data' => ['pjax' => true]],
    ]);

    $groupListTmp = $groupList;
    $groupListTmp[0] = '---';
    echo '<div class="col pb-3">'
    .$form->field($model, 'edit_group_id')->dropdownList( $groupListTmp, [ 'value' => $editedData['group_id'], 'onchange' => 'this.form.submit()' ] )->label('Выберите группу для редактирования').
    '</div>';

    ActiveForm::end();

    if( !isset($edited_data[0]) || !is_array($edited_data[0]) || empty($edited_data[0]) ) {

        echo "</div>
        </div>
        ";

        return;
    }

    echo '<hr class="hr">';

    $form = ActiveForm::begin([
        //'options' => ['data' => ['pjax' => true]],
    ]);

    
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


?>

    <div class="conteiner">
        <?= $form->field($model, 'group_id')->hiddenInput( [ 'value' => $editedData['group_id'] ] )->label(''); ?>
        <div class="col pb-3">
            <?= $form->field($model, 'name')->textInput( [ 'value' => $editedData['name'] ] )->label('Наименование группы'); ?>
        </div>
        <div class="col pb-3">
            <?= $form->field($model, 'parent_group_id')->dropdownList( $groupList, [ 'value' => $editedData['parent_group_id'] ])->label('Родительская группа'); ?>
        </div>
        <div class="col pb-3">
            <?= $form->field($model, 'status')->radioList( 
                $statusList,
                [ 'value' => $editedData['status'] ],
            )->label('Статус группы'); ?>
        </div>
        <div class=\"col pt-5\">
            <?= Html::submitButton('Изменить', ['class' => 'btn btn-primary', 'name' => 'action', 'value' => 'change' ])?> &nbsp; <?= Html::submitButton('Удалить', 
            [
                'class' => 'btn btn-danger','name' => 'action', 'value' => 'delete',
                'data' => [ 'confirm' => 'Вы уверены что хотите удалить группу?' ],
            ])?>
        </div>

    </div>

    <?php ActiveForm::end() ?>

    </div>
</div>

