<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Bookcatalog. Подробно о книге';

echo "<div class=\"bookdescription_back_link pb-3\"><a href=\"".Url::to(['bookcatalog/index', 'id' => $bookData[0]['group_id'] ])."\">[вернуться]</a></div>";

if(!empty($bookData[0]['authors'])) {

    $authorList = [];

    foreach($bookData[0]['authors'] as $authorData) {

        $authorList[] = $authorData['author_full_name'];
    }

    if(!empty($authorList)) {
        echo "<div class=\"bookdescription_author pb-3\">";
        echo implode(', ', $authorList);
        echo "</div>";
    }
}


echo "<h4 class=\"bookdescription_title pb-3\">{$bookData[0]['title']}</h4>";

echo "
<div class=\"conteiner\">
    <div class=\"row\">
        <div class=\"col-4\">
";

    if( !empty($bookData[0]['image']) ) {
        echo "<img src=\"".Yii::getAlias('@web').'/images/books/'.$bookData[0]['image']."\" class=\"bookdescription_img\">";
    }
    else {
        echo "<img src=\"/web/images/books/no-img.png\" class=\"image\">";
    }

echo "
       
        </div>
        <div class=\"col-8\">{$bookData[0]['description']}<br><br>
        ISBN: {$bookData[0]['isbn']}<br>
        Год издания: {$bookData[0]['year']}
        <br><br>
";

if (!Yii::$app->user->isGuest) {
    echo '<a href="'.Url::to(['admin/create_book', 'edit_book_id' => $bookData[0]['book_id'] ]).'"><button type="button" class="btn btn-primary btn-sm">Редактировать</button></a><br>';
}

echo "
        </div>
    </div>
</div>
";

