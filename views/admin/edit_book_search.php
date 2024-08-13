<?php

    use yii\helpers\Html;
    use yii\helpers\Url;
    use yii\widgets\ActiveForm;
    use app\models\BookcatalogModel;
    use yii\bootstrap5\Alert;

    $this->title = 'Bookcatalog. Поиск книги для редактирования';

    $bookModel = new BookcatalogModel();

    echo $this->render('@app/views/admin/nav');

    $search_words = $search_words ?? '';

    echo '
<div class="conteiner">
    <div class="col">
    <h4 class="pt-3 pb-3">Поиск книги для редактирования</h4>
    ';

    $form = ActiveForm::begin([
        'options' => [
            'action' => ['/web/admin/edit_book_search','id' => '101'],
            'data' => ['pjax' => true], 
            'options' => ['enctype' => 'multipart/form-data'],
            //'onchange' => 'this.form.submit()'
        ],
    ]);

?>

<div class="conteiner">

    <div class="d-flex flex-row">

        <div class="col-10 pb-4"><?= $form->field($model, 'search_book')->textInput([ 'value' => $search_words ])->label('Введите часть наименования, ID или ISBN книги'); ?></div>

        <div class="col-2 pb-4 ps-2" style="padding-top:39px;">

        <?= Html::submitButton('Найти', 
                [
                    'class' => 'btn btn-primary btn-sm ps-3 pe-3', 'name' => 'search', 'style' => 'height:36px;', 'value' => 'delete'
        ])?>    

        </div>
    </div>

<?php

if( isset($book_list) && !empty($book_list) ) {

    echo "<h5>Результат поиска</h5>";

    foreach($book_list as $booksData) {

        
        echo '<a href="'.Url::to(['admin/create_book', 'edit_book_id' => $booksData['book_id'] ]).'">'.$booksData['authors_full_name'].'. "'.$booksData['title'].'"</a><br>';
    }


}


?>

</div>

<?php ActiveForm::end() ?>
