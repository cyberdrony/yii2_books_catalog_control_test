<?php

namespace yii\web\View;

use Yii;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\BookcatalogModel;
use yii\bootstrap5\Alert;


$this->title = 'BookCatalog. Каталог книг';

echo '<div class="grid">
    <div class="row">
';

if( isset( $Content['Booklist'] ) && is_array($Content['Booklist']) && !empty($Content['Booklist'])) {

    foreach($Content['Booklist'] as $BookElem) {

        echo $this->render('@app/views/bookcatalog/showbookcard', [
            'Content' => $BookElem,
        ]);
    }
}
else {
    echo "&nbsp; В данной группе книг не обнаружено";
}

echo '</div></div>';


?>



