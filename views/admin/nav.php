<?php

namespace yii\web\View;

use yii\helpers\Url;

?>


<div class="conteiner">
  <div class="col mb-3">
    <form>
      <a href="<?=Url::to(['admin/create_group']);?>"><button class="btn btn-secondary btn-sm ms-0 me-2" type="button">Создать группу</button></a>
      <a href="<?=Url::to(['admin/edit_group']);?>"><button class="btn btn-secondary btn-sm me-4" type="button">Редактировать группу</button></a>

      <a href="<?=Url::to(['admin/create_book']);?>"><button class="btn btn-success btn-sm me-2" type="button">Добавить книгу</button></a>
      <a href="<?=Url::to(['admin/edit_book_search']);?>"><button class="btn btn-success btn-sm me-4" type="button">Редактировать книгу</button></a>
      
    </form>
  </div>
</div>