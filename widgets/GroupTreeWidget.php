<?php

namespace app\widgets;

use Yii;
use yii\helpers\Url;
use app\models\BookcatalogModel;

class GroupTreeWidget {

    private $groupListOut, $bookModel;

    public function __construct()
    {
        $this->bookModel = new BookcatalogModel();
    }

    public function formatingGroupList( array $groupArr = [], int $parentId = 0, int $level = 0, string $for = 'select' ) {

        if( !isset($groupArr) || empty($groupArr) || empty($groupArr[$parentId]) ) {
            return [];
        }

        $actGroupId = 0;

        if( Yii::$app->request->get() !== null ) {
            $actGroupId = Yii::$app->request->get( 'id' );
        }   

        foreach( $groupArr[$parentId] as $groupId => $groupName ) {

            $bookCounter = $this->bookModel->getBooksInGroupCounter($groupId);
            $bookCounter = $bookCounter > 0 ? " (".$bookCounter.")"  : '';

            if( $for == 'select' ) {
                $groupName = html_entity_decode(str_repeat("&#160;", $level * 3)).$groupName;
            }
            else {

                $actClass = "";

                if( $actGroupId == $groupId ) {
                    $actClass = "catalog_group_act_menu";
                }

                $groupName = "
                    <a href=\"".Url::to(['bookcatalog/index', 'id' => $groupId ])."\" class=\"group_link\">
                        <div class=\"p-2 catalog_group_level_$level $actClass\">".$groupName." $bookCounter</div>
                    </a>
                ";
            }

            $this->groupListOut[$groupId] = "$groupName";

            if( isset($groupArr[$groupId]) && is_array($groupArr[$groupId]) && !empty($groupArr[$groupId]) ) {
                ++$level;
                self::formatingGroupList( $groupArr, $groupId, $level, $for );
                --$level;
            }
        }

        return $this->groupListOut;
    }
        
}

