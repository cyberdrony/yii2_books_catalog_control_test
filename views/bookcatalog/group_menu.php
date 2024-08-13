<?php

use app\models\BookcatalogModel;
use app\widgets\GroupTreeWidget;
use yii\helpers\Url;

$Out = '';
$bookcatalogModel = new BookcatalogModel;

$groupListRes = $bookcatalogModel->selectData([
    'table_name' => 'groups',
    'where_data'    => [
        'status' => 1,
    ]
]);

$groupListArr = [];

foreach($groupListRes as $groupListData) {

    $groupListArr[$groupListData['parent_group_id']][$groupListData['group_id']] = $groupListData['name'];
}

$GroupTreeWidget = new GroupTreeWidget;
$groupList = $GroupTreeWidget->formatingGroupList($groupListArr, 0, 0, 'div');

foreach( $groupList as $id => $groupElem ) {
    $Out .= $groupElem;
}

echo $Out;

?>


