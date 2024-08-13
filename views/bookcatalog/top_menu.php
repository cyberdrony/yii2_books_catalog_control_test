<?php

    use yii\helpers\Url;
    use yii\widgets\ActiveForm;
    use app\models\BookcatalogModel;

    $serach_words = '';

    if ( Yii::$app->request->post('BookcatalogBookForm') ) {
        $form = Yii::$app->request->post();
        if( isset($form['BookcatalogBookForm']['catalogbook_search']) && !empty($form['BookcatalogBookForm']['catalogbook_search']) ) {
            $serach_words = $form['BookcatalogBookForm']['catalogbook_search'];
        }
    }

?>

<div class="conteiner">
    <div class="row">
        <div class="col position-relative p-3 border-bottom top_menu">
            <div class="top_menu_title">

                <div class="hstack gap-3">

                    <div class="col-3">
                        <a href="/<?=\Yii::$app->defaultRoute?>">Самый лучший каталог книг</a>
                    </div>

                    <div class="col-8">

                        <?php
                            $form = ActiveForm::begin([
                                'action'     => Url::to(['bookcatalog/search']),
                                'method'    => 'post'
                            ]);

                            echo '
                            <div class="input-group">
                            ';
                                echo $form->field($model, 'catalogbook_search')->textInput( [ 'value' => $serach_words, 'class' => 'form-control rounded-end-0 top_menu_search_input', 'placeholder' => "Поиск по книгам"] )->label(false);
                                echo '<button class="btn btn-outline-secondary btn-sm" type="submit">Поиск</button>
                            </div>
                            ';                           
                            ActiveForm::end();
                        ?>
                      
                    </div>
<?php

    if(!empty(\Yii::$app->user->isGuest)) {

        echo "<div class=\"col-1\">
            <a href=\"".Url::to(['bookcatalog/login'])."\"><button type=\"button\" class=\"btn btn-secondary btn-sm position-absolute top_menu_login_button\">Войти</button></a>
        </div>";
    }

?>

                </div>
            </div>
            

<?php

echo '<div class="position-absolute  bottom-0 end-0 mb-0 p-0">';

if( empty(\Yii::$app->user->isGuest) && !empty(\Yii::$app->user->identity->username) ) {

    echo '  <div class="position-relative top-0 top_menu_auth_info pb-2">
                Вы вошли как "'.\Yii::$app->user->identity->username.'" &nbsp; [ <a href="'.Url::to(['bookcatalog/logout']).'">Выйти</a> ]
            </div>
            <div class="position-relative border-top border-start border-end rounded-top p-2 ps-4 pe-4 text-bg-light">
                <a href="'.Url::to(['admin/index']).'" class="link-dark">Администрирование</a>
            </div>
    ';
}

echo '</div>';

?>

        </div>
    </div>
</div>
