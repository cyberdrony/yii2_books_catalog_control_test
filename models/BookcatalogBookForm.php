<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class BookcatalogBookForm extends Model
{
    public $title;
    public $description;
    public $year;
    public $author_id;
    public $isbn;
    public $status;
    public $group_id;
    public $imageFile;
    public $search_book;
    public $new_author_lname, $new_author_fname, $new_author_sname;
    public $catalogbook_search;

    const SCENARIO_DELETE_BOOK = 'delete_book';

    public function rules()
    {

        return [
            
            [['title', 'description', 'year', 'isbn'], 'required', 'message' => "Поле обязательное для заполнения" ],
            [['group_id'], 'compare', 'compareValue' => 0, 'operator' => '>', 'message' => "Поле обязательное для заполнения" ],
            [['year'], 'number', 'message' => "Год должен быть числом" ],
            [['year'], 'compare', 'compareValue' => Date('Y'), 'operator' => '<=', 'message' => 'Год не может превышать '.Date('Y') ],
            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg'],
            [['isbn'], 'match', 'pattern' => '/^(?=(?:\D*\d){10}(?:(?:\D*\d){3})?$)[\d-]+$/', 'message' =>'Это не ISBN код'],
            ['author_id', 'required', 'message' => "Выберите автора или добавьте нового", 'when' => function ($model) {
                return ($model->new_author_lname == '' || $model->new_author_fname == '');
            }, 'whenClient' => "function (attribute, value) {
                return ( $('#bookcatalogbookform-new_author_lname').val() == '' && $('#bookcatalogbookform-new_author_fname').val() == '' );
            }"],
            [['new_author_lname', 'new_author_fname'], 'required', 'message' => "Поле обязательное для заполнения", 'when' => function ($model) {
                return $model->author_id == '';
            }, 'whenClient' => "function (attribute, value) {
                return $('#bookcatalogbookform-author_id').val() == '';
            }"],
        ];

    }

    public function uploadImage( string $fileName = '' )
    {
        if(!empty($fileName)) {

            $this->imageFile->saveAs(Yii::$app->basePath.'/web/images/books/' . $fileName);
            return true;
        }
        return false;
    }

}