<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\helpers\Url;

use app\models\BookcatalogModel;
use app\models\BookcatalogGroupForm;
use app\models\BookcatalogBookForm;
use app\widgets\GroupTreeWidget;
use yii\web\UploadedFile;
use app\components\SimpleTrigramSearch;


class AdminController extends Controller
{

    /**
     * Контроллер админки.
     * 
     */

    public $Common = [], $book_group_form_model, $book_form_model, $book_model;

    /**
     * Это можно было бы вывести в конфиг, но используется только здесь, так что эти "костыли" допустимы
    */
    const STATUS_LIST = [
        '1' => 'Видна',
        '0' => 'Скрыта',
    ];

    /**
     * Сюда гостям доступ закрыт
     * 
     * @param void
     * @return array
     */

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => false,
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        $this->book_group_form_model = new BookcatalogGroupForm();
        $this->book_form_model = new BookcatalogBookForm();
        $this->book_model = new BookcatalogModel();

        return parent::beforeAction($action);
    }

    
    /**
     * Пустая заглавная страница админки
     * 
     * @param void
     * @return string
     */


    public function actionIndex() : string
    {
        return $this->render('/admin/index', [
            'Content' => [

            ]
        ]);

    }

    /**
     * Страница создания группы книг
     * 
     * @param void
     * @return string
     */

    public function actionCreate_group() : string
    {

        // Всё дерево групп каталога
        $groupList = self::_getGroupList();

        // Во вьюхе есть алерт, который реагирует на успешное сохранение. Определяем как null чтобы не был isset
        $saveRes = null;

        /**
        * Проверяем post. Если есть, то пишем в БД. Все проверки там.
        */
        if ($this->book_group_form_model->load(Yii::$app->request->post()) && $this->book_group_form_model->validate()) {

            $saveRes = $this->book_model->insertData([
                'table_name' => 'groups',
                'columns' => Yii::$app->request->post('BookcatalogGroupForm'),
            ]);

        }

        return $this->render('/admin/create_group', [
            'model'         => $this->book_group_form_model,
            'groupList'     => $groupList,
            'statusList'    => self::STATUS_LIST,
            'save_result'   => $saveRes,
        ]);
    }


    /**
     * Страница редактирования группы книг
     * 
     * @param void
     * @return string
     */

    public function actionEdit_group() : string
    {

        // Всё дерево групп каталога
        $groupList = self::_getGroupList();

        $saveRes = null;
        $editedGroupId = 0;

        if( $this->book_group_form_model->load(Yii::$app->request->post()) ) {
            
            /**
            * Проверяем post. Если есть, то пишем в БД изменения. Все проверки там.
            */

            $editDataForm = Yii::$app->request->post();
            $editedGroupId = $editDataForm['BookcatalogGroupForm']['edit_group_id'] ?? 0;

            /**
             * Во вьюхе есть 2 сценария обращения к данному методу.
             * Если выводим редактируему группу, то сценарий иной
             */

            if( !empty($editedGroupId) ) {
                $this->book_group_form_model->scenario = $this->book_group_form_model::SCENARIO_GROUPS_FORMS;
            }

            if($this->book_group_form_model->validate()) {

                /**
                 * Здесь проверяем post-ы. Если соответсвуют процессу изменения, то апдейтим/делетим группу.
                 */

                if( isset($editDataForm["BookcatalogGroupForm"]["group_id"]) && !empty($editDataForm["BookcatalogGroupForm"]["group_id"]) ) {

                    $group_id = $editDataForm["BookcatalogGroupForm"]["group_id"];
                    $editedGroupId = $group_id;

                    // Целевую группу из массива удаляем, т.к. в БД это премьерный ключ
                    unset($editDataForm["BookcatalogGroupForm"]["group_id"]);

                    if( $editDataForm["action"] == 'change' ) {

                        if($editDataForm["BookcatalogGroupForm"]['parent_group_id'] != $group_id ) {

                            $saveRes = $this->book_model->updateData([
                                'table_name'    => 'groups',
                                'columns'       => $editDataForm["BookcatalogGroupForm"],
                                'where'         => "group_id = $group_id",
                            ]);
                        }
                    }
                    elseif( $editDataForm["action"] == 'delete' ) {

                        $saveRes = $this->book_model->deleteGroup( $group_id );
                    }
                }
            }
            else {
                echo "No valid";
            }
        }

        $groupListRes = [];

        if(!empty($editedGroupId)) {

            $groupListRes = $this->book_model->selectData([
                'table_name'    => 'groups',
                'where_data'    => [
                    'group_id'      => $editedGroupId,
                ]
            ]);
        }

        return $this->render('/admin/edit_group', [
            'model'         => $this->book_group_form_model,
            'groupList'     => $groupList,
            'statusList'    => self::STATUS_LIST,
            'save_result'   => $saveRes,
            'edit_group'    => $editedGroupId,
            'edited_data'   => $groupListRes,
        ]);


    }

    /**
     * Страница поиска книги для редактирования
     * 
     * @param void
     * @return string
     */

    public function actionEdit_book_search() : string
    {
        $res = [];
        $searchWords = '';

        if ($this->book_form_model->load(Yii::$app->request->post())) {

            // Это слово для поиска книги
            $searchWordsForm = Yii::$app->request->post();

            if( isset( $searchWordsForm['BookcatalogBookForm']['search_book']) && !empty($searchWordsForm['BookcatalogBookForm']['search_book']) ) {
                $searchWords = $searchWordsForm['BookcatalogBookForm']['search_book'];

                // Если только цифры, то пробуем искать по ID и ISBN
                if( preg_match('/^\d+$/', $searchWords) ) {

                    $res = $this->book_model->selectData([
                        'table_name'    => 'books',
                        'where_data'    => [ 
                            'book_id' => $searchWords,
                        ],
                    ]);

                    if( empty($res) ) {

                        $res = $this->book_model->selectData([
                            'table_name'    => 'books',
                            'where_data'    => [ 
                                'isbn' => $searchWords,
                            ],
                        ]);
                    }
                }
                else {

                    // Если слово - триграммный поиск
                    $search = new SimpleTrigramSearch;
                    $res = $search->search($searchWords);
                }
            }
        }
 
        $booksData = $this->book_model->getBookFullData($res);
          
        return $this->render('/admin/edit_book_search', [
            'model'         => $this->book_form_model,
            'book_list'     => $booksData,
            'search_words'  => $searchWords,
        ]);
 
     }


    /**
     * Cоздание/редактирования книги
     * 
     * @param void
     * @return string
     */ 

    public function actionCreate_book() : string
    {

        $this->book_form_model = new BookcatalogBookForm();
        $saveRes = null;
        $bookData = [];
        $form = [];
        $authorsList = [];
        $edited_book_id = 0;

        if($this->book_form_model->load(Yii::$app->request->post())) {
            $form = Yii::$app->request->post('BookcatalogBookForm');
        }

        $edited_book_id = Yii::$app->request->get( 'edit_book_id' );
   
        // Получаем группы
        $groupList = self::_getGroupList();
        $groupList[0] = '---';         // В селекте меняем первый элемент "Корневой каталог", т.к. здесь он не нужен

        // Получаем всех авторов
        $authors = $this->book_model->selectData([
            'table_name'    => 'authors',
        ]);

        if( !empty($authors) ) {

            foreach($authors as $authorData) {
                $authorsList[$authorData['author_id']] = $authorData['lname']." ".$authorData['fname']." ".$authorData['sname'];
            }

            // Сортируем здесь, чтобы не грузить БД
            asort($authorsList);
        }

        if ( !empty($edited_book_id) ) {
            
            // Процесс редактирования книги
            self::_editBookProcess($form, $edited_book_id);
        }
        elseif (!empty($form) && $this->book_form_model->validate()) {
            
            // Процесс создание новой книги
            self::_createBookProcess($form);
        }

        if( !empty($edited_book_id) ) {
            $bookData = $this->book_model->getBookFullData([ $edited_book_id ]);
        }

        return $this->render('/admin/create_book', [
            'model'         => $this->book_form_model,
            'statusList'    => self::STATUS_LIST,
            'authors'       => $authorsList,
            'save_result'   => $saveRes,
            'groupList'     => $groupList,
            'edit_data'     => $bookData,
        ]);
    }

    /**
     * Процесс редактирования книги
     * 
     * @param array - формы, которые содержат данные книги
     * @return int
     */ 


    private function _createBookProcess( array $form = [] ) : int {
        
        $saveRes = 0;

        $newBookId = $this->book_model->insertData([
            'table_name' => 'books',
            'columns' => $form,
        ]);

        // Если книжка успешно добавилась, пишем дальше
        if( !empty($newBookId) ) {

            $form['book_id'] = $newBookId;

            // ...привязываем к группе
            $saveRes = $this->book_model->insertData([
                'table_name' => 'books_in_groups',
                'columns' => $form,
            ]);

            // ...авторы книжки
            self::_addAuthorToBook($form);

            // ...фотка книжки
            self::_addImageToBook($form);
        }

        return $saveRes;
    }

    /**
     * Процесс редактирования книги
     * 
     * @param array - формы, которые содержат данные книги
     * @param int - ID редактируемой книги
     * @return int
     */ 

    private function _editBookProcess( array $form = [], int $edited_book_id = 0) {

        $saveRes = 0;

        if( !empty(Yii::$app->request->post('change_book')) ) {

            // Процесс изменения книги

            $saveRes += $this->book_model->updateData([
                'table_name'    => 'books',
                'columns'       => $form,
                'where'         => "book_id = $edited_book_id",
            ]);
            
            $saveRes += $this->book_model->updateData([
                'table_name'    => 'books_in_groups',
                'columns'       => $form + ['book_id' => $edited_book_id],
                'where'         => "book_id = $edited_book_id",
            ]);

            // ...авторы книжки
            $saveRes += self::_addAuthorToBook($form + [ 'book_id' => $edited_book_id ]);

            // ...фотка книжки
            $saveRes += self::_addImageToBook($form + [ 'book_id' => $edited_book_id ]);

        }
        elseif(!empty(Yii::$app->request->post('delete_book'))) {

            // Процесс удаления книги

            $saveRes = $this->book_model->deleteBook( $edited_book_id );

            // Удаляем картинку
            $resImg = $this->book_model->selectData([
                'table_name'        => 'books',
                'select_columns'    => ['image'],
                'where_data'        => [ 
                    'book_id' => $edited_book_id,
                ],
            ]);

            if( isset($resImg[0]) && isset($resImg[0]['image']) && !empty($resImg[0]['image'] ) ) {

                $pathFile = \Yii::getAlias('@webroot') .'/images/books/'.$resImg[0]['image']; 

                if(file_exists($pathFile)) {
                    unlink($pathFile);
                }
            }          

            return Yii::$app->response->redirect(Url::to(['bookcatalog/index', 'id' => $form['group_id']]));
        }

        if( $saveRes == 5 ) {
            return 1;
        }
        else {
            return 0;
        }
    }

    /**
     * Процесс добавления автора в книгу
     * 
     * @param array - формы, которые содержат данные книги
     * @return int
     */ 

    private function _addAuthorToBook( array $form = [] ) : int {

        $saveRes = 0;

        if( isset($form['new_author_lname']) && isset($form['new_author_fname']) && !empty($form['new_author_lname']) && !empty($form['new_author_fname']) ) {

            $form['new_author_sname'] = (isset($form['new_author_sname']) && !empty($form['new_author_sname'])) ? $form['new_author_sname'] : '';

            // пишем нового автора
            $form['author_id'] = [
                $this->book_model->insertData([
                    'table_name' => 'authors',
                    'columns' => [
                        'fname' => $form['new_author_fname'],
                        'sname' => $form['new_author_sname'],
                        'lname' => $form['new_author_lname'],
                    ],
                ])
            ];
        }

        // ...привязываем к автору
        $saveRes += $this->book_model->deleteData([
            'table_name' => 'books_authors',
            'where'         => "book_id = '{$form['book_id']}'",
        ]);

        foreach($form['author_id'] as $author_id) {

            $formTmp = $form;
            $formTmp['author_id'] = $author_id;

            $saveRes += $this->book_model->insertData([
                'table_name' => 'books_authors',
                'columns' => $formTmp + [ 'author_id' => $author_id ],
            ]);
        }

        if( $saveRes == count($form['author_id']) + 1 ) {
            return 1;
        }
        else {
            return 0;
        }
    }

    /**
     * Процесс добавления фото книги
     * 
     * @param array - формы, которые содержат данные книги
     * @return int
     */ 

    private function _addImageToBook( array $form = [] ) : int {

        $saveRes = 0;

        $this->book_form_model->imageFile = UploadedFile::getInstance($this->book_form_model, 'imageFile');

        if(isset($this->book_form_model->imageFile)) {
            
            $img_name = 'book_'.$form['book_id'].'.'.$this->book_form_model->imageFile->extension;

            if ($this->book_form_model->uploadImage( $img_name )) {                   
                
                $saveRes = $this->book_model->updateData([
                    'table_name' => 'books',
                    'columns' => [
                        'image' => $img_name
                    ],
                    'where'     => ['book_id' => $form['book_id'] ]
                ]);
            }
        }

        return $saveRes;
    }


    /**
     * Формирование дерева групп.
     * Переносить в компоненты нет смысла, т.к. используется только здесь
     * 
     * @param void
     * @return array
     */

    private function _getGroupList() : array {

        // Само дерево в виджите
        $GroupTreeWidget = new GroupTreeWidget;

        // Получаем данные
        $groupListRes = $this->book_model->selectData([
            'table_name'    => 'groups',
        ]);

        $groupListArr = [];

        // Строем дерево
        foreach($groupListRes as $groupListData) {

            $groupListArr[$groupListData['parent_group_id']][$groupListData['group_id']] = $groupListData['name'];
        }

        $groupList = [];
        $groupList[0] = "Корневая группа";

        // Форматируем данные
        $groupList = $groupList + $GroupTreeWidget->formatingGroupList($groupListArr, 0);

        return $groupList;

    }


}