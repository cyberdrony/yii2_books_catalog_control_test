<?php

use yii\helpers\Url;

$authorList = [];

if(!empty($Content['authors'])) {

    foreach($Content['authors'] as $authorData) {

        $authorList[] = $authorData['author_full_name'];
    }
}

$hidden_book = '';

if($Content['status'] == 0) {
    $hidden_book = "bookcard_hidden_book";
}

$authors = "<div class=\"bookcard_author pb-1 $hidden_book\">";
$authors .=  implode(', ', $authorList) ?? '&nbsp;';
$authors .= "</div>";


echo '<div class="col bookcard pb-3 $hidden_book">
<div class="bookcard_photo $hidden_book">
<a href="'.Url::to(['bookcatalog/book_card', 'book_id' => $Content['book_id'] ]).'">';

if( isset($Content['image']) ) {
    echo "<img src=\"/web/images/books/{$Content['image']}\" class=\"image $hidden_book\">";
}
else {
    echo "<img src=\"/web/images/books/no-img.png\" class=\"image $hidden_book\">";
}


echo '
</a>
</div>
<div class="bookcard_title overflow-hidden $hidden_book">
';

echo $authors;

echo '
<a href="'.Url::to(['bookcatalog/book_card', 'book_id' => $Content['book_id'] ]).'" class="'.$hidden_book.'">
'.$Content['title'].'
</a>
</div>
</div>
</a>

';

