<?php

namespace app\models;

use yii\base\Model;
use yii\db\ActiveRecord;

class BookcatalogGroupForm extends Model
{
    public $name;
    public $parent_group_id;
    public $edit_group_id;
    public $status;

    const SCENARIO_GROUPS_FORMS = 'load_groups';

    public function rules()
    {
        return [
            [
                ['name'], 'required', 'message' => "'Наименование группы' обязательно для заполнения",
            ],

        ];
    }


    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_GROUPS_FORMS] = [];
        return $scenarios;
    }


}


