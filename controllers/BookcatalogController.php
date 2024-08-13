<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\BookcatalogModel;
use app\models\LoginForm;
use app\components\SimpleTrigramSearch;


class BookcatalogController extends Controller
{

    /**
     * Контроллер основного отображения каталога
     */

    public $Common = [], $bookModel;

    public function beforeAction($action)
    {
        $this->bookModel = new BookcatalogModel();
        return parent::beforeAction($action);
    }

    /**
     * Основная страница. Смотрим Id группы и выводим книги из этой группы
     * 
     * @param void
     * @return string
     */

    public function actionIndex() : string
    {

        $groupId = Yii::$app->request->get('id') ? Yii::$app->request->get('id') : 0;
        $groupIds = $this->bookModel->getGroupChild($groupId);

        $where_params = ['group_id'  => $groupIds ];

        // Если гость, то показываем только не скрытые книги
        if (Yii::$app->user->isGuest) {
            $where_params = $where_params + [ 'status' => 1 ];
        }

        $bookIdList = [];

        $bookIdList = $this->bookModel->selectData( [
            'table_name' => 'books_in_groups',
            'where_data' => $where_params,
        ]);

        $BookIds = [];
        foreach($bookIdList as $IdsData ) {
            $BookIds[] = $IdsData['book_id'];
        }

        // Список ID книг получен, вытаскиваем данные
        $bookList = !empty($BookIds) ? $this->bookModel->getBookFullData( $BookIds ) : [];
        $auth_list = [];

        foreach ($bookList as $i => $row) {
            foreach ($row['authors'] as $author) {
                if(!isset($auth_list[$i])) {
                    $auth_list[$i] = $author['author_full_name'];
                }
            }
        }

        array_multisort($auth_list, SORT_ASC, $bookList);

        return $this->render('index', [
            'Content' => [
                'Booklist' => $bookList,
            ]
        ]);
        
    }

    /**
     * Страница описания книги
     * 
     * @param void
     * @return string
     */

    public function actionBook_card() : string
    {
        $bookId = Yii::$app->request->get('book_id');

        if( !empty($bookId) ) {
            $bookData = !empty($bookId) ? $this->bookModel->getBookFullData( [$bookId] ) : [];
        }

        return $this->render('book_description', [
            'bookData' => $bookData,
        ]);
        
    }


    public function actionSearch() : string {

        $bookList = [];
        $searchWords = '';

        if ( Yii::$app->request->post('BookcatalogBookForm') ) {
            $form = Yii::$app->request->post();

            if( isset($form['BookcatalogBookForm']['catalogbook_search']) && !empty($form['BookcatalogBookForm']['catalogbook_search']) ) {

                $searchWords = $form['BookcatalogBookForm']['catalogbook_search'];

                $search = new SimpleTrigramSearch;
                $searchRes = $search->search($searchWords);
        
                $bookList = !empty($searchRes) ? $this->bookModel->getBookFullData( $searchRes ) : [];
            }
        }

        return $this->render('index', [
            'Content' => [
                'Booklist' => $bookList,

            ]
        ]);
    }


    /**
     * Страница логина. Не было времени заморачиваться кастомным логином, так что взял из коробки
     * @param void
     * @return array
     */

    public function actionLogin() : string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect( '/'.\Yii::$app->defaultRoute );
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect( '/'.\Yii::$app->defaultRoute );
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Страница разлогина. Не было времени заморачиваться кастомным логином, так что взял из коробки
     * @param void
     * @return array
     */

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->redirect( '/'.\Yii::$app->defaultRoute );
    }


}