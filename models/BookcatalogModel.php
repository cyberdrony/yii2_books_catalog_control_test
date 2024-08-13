<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Expression;

class BookcatalogModel extends Model
{

    /**
     * Модель с методами работающими с БД.
     * 
     */

    public $query, $imageFile;

    public function __construct() {

        $this->query = new \yii\db\Query();
    }

    /**
     * "Посредник". Универсальный метод для запроса SELECT в БД 
     * 
     * @param array - данные для исполнения запроса
     * @return array
     */

    public function selectData( $param ) : array {

        $needParam = [ 'table_name' ];


        if( isset($param['select_columns']) && is_array($param['select_columns']) ) {

            foreach($param['select_columns'] as $i => $col_name) {

                $table = Yii::$app->db->schema->getTableSchema($param['table_name']);

                if( !isset($table->columns[$col_name]) ) {
                    unset($param['select_columns'][$i]);
                }
            }
        }
        else {
            $param['select_columns'] = ['*'];
        }

        $this->query->select( $param['select_columns'] )->from( $param['table_name'] );

        if( isset($param['where_data']) && is_array($param['where_data'])) {

            foreach( array_keys($param['select_columns']) as $col_name ) {

                $table = Yii::$app->db->schema->getTableSchema($param['table_name']);

                if( !isset($table->columns[$col_name]) ) {
                    unset($param['where_data'][$col_name]);
                }
            }

            if( !empty($param['where_data']) ) {

                $this->query->where( $param['where_data'] );
            }
        }
        
        $res = $this->query->all();

        return $res;
    }

    /**
     * Универсальный метод для запроса INSERT в БД 
     * 
     * @param array - данные для исполнения запроса
     * @return int
     */

    public function insertData( $param ) : int{

        $Res = 0;
        $needParam = [ 'table_name', 'columns' ];


        if( is_array($param['columns']) ) {

            $vals = [];

            foreach($param['columns'] as $col_name => $val) {

                $table = Yii::$app->db->schema->getTableSchema($param['table_name']);

                if (isset($table->columns[$col_name])) {
                    $vals[$col_name] = $val;
                }
            }

            if( !empty($vals) ) {
                Yii::$app->db->createCommand()->insert( $param['table_name'], $vals )->execute();
                $Res = Yii::$app->db->getLastInsertID();
            }
        }

        return $Res;
    }

    /**
     * Универсальный метод для запроса UPDATE в БД 
     * 
     * @param array - данные для исполнения запроса
     * @return int
     */

    public function updateData( $param ) {

        $Res = 0;
        $needParam = [ 'table_name', 'columns', 'where' ];


        if( is_array($param['columns']) ) {

            $vals = [];

            foreach($param['columns'] as $col_name => $val) {

                $table = Yii::$app->db->schema->getTableSchema($param['table_name']);

                if (isset($table->columns[$col_name])) {
                    $vals[$col_name] = $val;
                }
            }

            if( !empty($vals) ) {

                $Res = Yii::$app->db->createCommand()->update( $param['table_name'], $vals, $param['where'] )->execute();
            }
        }

        return $Res;
    }

    /**
     * Универсальный метод для запроса DELETE в БД 
     * 
     * @param array - данные для исполнения запроса
     * @return int
     */

    public function deleteData( $param ) {

        $Res = 0;
        $needParam = [ 'table_name', 'where' ];

        if( !empty($param['where']) ) {

            $Res = Yii::$app->db->createCommand()->delete( $param['table_name'], $param['where'] )->execute();
        }

        return $Res;
    }

    /**
     * Список дочерних групп
     * 
     * @param int - ID родительской группы
     * @return array - список дочерних ID
     */

    public function getGroupChild( int $parent_id = 0 ) : array {

        static $childGroupList = [];
        $childGroupList[] = $parent_id;

        $Res = self::selectData([
            'table_name'        => 'groups',
            'select_columns'    => ['group_id'],
            'where_data'        => [
                'parent_group_id' => $parent_id,
            ],
        ]);

        if( !empty($Res) ) {
            foreach($Res as $Data) {
                self::getGroupChild($Data['group_id']);
            }
        }

        return $childGroupList;
    }
    
    /**
     * Список ID книг по ID группы
     * 
     * @param int - ID группы
     * @return array - список дочерних ID книг
     */

    public function getBooksIdsByGroupId( int $group_id = 0 ) : array {

        return self::selectData([
            'table_name'        => 'books_in_groups',
            'select_columns'    => ['book_id'],
            'where_data'        => [
                'group_id' => $group_id,
            ],
        ]);
    }

    /**
     * Счётчик кол-ва книг в группе
     * 
     * @param int - ID группы
     * @return int - кол-во книг в группе
     */

    public function getBooksInGroupCounter( int $group_id = 0 ) {

        $params = ['group_id' => $group_id];

        // Если гость, то показываем только открытые
        if (Yii::$app->user->isGuest) {
            $params = $params + [ 'status' => 1 ];
        }

        return (new \yii\db\Query())
        ->from('books_in_groups')
        ->where( $params )
        ->count();
    }

    /**
     * Удаляет группу
     * 
     * @param int - ID группы
     * @return int
     */

    public function deleteGroup( int $groupId = 0 ) : int {      

        if (empty($groupId)) return 0;

        $clearedTables = ['books', 'books_in_groups', 'books_authors', 'images', 'users_subscriptions'];

        $groupIds = self::getGroupChild( $groupId );
        if (empty($groupIds)) return 0;

        $transaction = Yii::$app->db->beginTransaction();

        try {

            self::deleteData([
                'table_name'    => 'groups',
                'where'         => "group_id in (".implode(', ', $groupIds).")",
            ]);
            
            foreach( $groupIds as $grId){

                $bookList = [];
                $bookListArr = self::getBooksIdsByGroupId( $grId );
    
                if( !empty($bookListArr) ) {
    
                    foreach( $bookListArr as $bookListData ) {
                        $bookList[] = $bookListData['book_id']; 
                    }
    
                    foreach( $clearedTables as $table ) {
    
                        self::deleteData([
                            'table_name'    => $table,
                            'where'         => "book_id in (".implode(', ', $bookList).")",
                        ]);
                    }
                }
            }

            $transaction->commit();

        } catch(\Exception $e) {

            $transaction->rollBack();
            throw $e;
        }      

        return 1;
    }

    /**
     * Удаляет книгу
     * 
     * @param int - ID книги
     * @return int
     */

    public function deleteBook( int $bookId = 0 ) {      

        if (empty($bookId)) return 0;

        $clearedTables = ['books', 'books_in_groups', 'books_authors', 'users_subscriptions'];

        $transaction = Yii::$app->db->beginTransaction();

        try {
    
            foreach( $clearedTables as $table ) {

                self::deleteData([
                    'table_name'    => $table,
                    'where'         => "book_id = '$bookId'",
                ]);
            }

            $transaction->commit();

        } catch(\Exception $e) {

            $transaction->rollBack();
            throw $e;
        }      

        return 1;
    }

    /**
     * Получает всю инфу книг по списку ID
     * 
     * @param int - список ID книг
     * @return array - результат
     */

    public function getBookFullData( array $bookIds = [] ) : array {

        $Res = [];

        if( !empty($bookIds) ) {
            
            $Res = Yii::$app->db->createCommand("SELECT DISTINCT 
                books.`book_id`, books.`title`, books.`year`, books.`description`, books.`isbn`, books.`image`, books.`status`, books_in_groups.`group_id`
                FROM books
                INNER JOIN books_in_groups ON books_in_groups.book_id = books.book_id
                WHERE books.book_id in ( ".implode(',', $bookIds).")  ORDER BY books.title
            ")->queryAll();

            foreach( $Res as $i => $Data ) {

                $Res2 = Yii::$app->db->createCommand("SELECT DISTINCT 
                    authors.`author_id`, CONCAT( COALESCE(`lname`,''), ' ', COALESCE(`fname`,''), ' ', COALESCE(`sname`,'') ) as author_full_name
                    FROM books_authors
                    INNER JOIN authors ON authors.author_id = books_authors.author_id
                    WHERE books_authors.book_id = '{$Data['book_id']}' ORDER BY author_full_name
                ")
                ->queryAll();

                if( !empty($Res2) ) {
                    $Res[$i] = $Res[$i] + [
                        'authors'   => $Res2
                    ];
                        
                    $authorList = [];
                    foreach($Res2 as $authorData) {
                        $authorList[] = $authorData['author_id'];
                    }

                    $Res[$i] = $Res[$i] + [
                        'author_id_list'   => $authorList,
                    ];
                }
            }
        }

        return $Res;

    }

    /**
     * Поиск книги
     * 
     * @param string - триграммный элемент
     * @return array - результат
     */

    public function BookSearch( string $trigramElem = '' ) {

        $trigramElem = preg_replace('/\s/', '', $trigramElem);

        $Res = [];

        $Res = Yii::$app->db->createCommand("SELECT *
        FROM `books` WHERE `title` LIKE '%".$trigramElem."%'")
            ->queryAll();

        $Res = $Res + Yii::$app->db->createCommand("SELECT *
        FROM `books` WHERE `description` LIKE '%".$trigramElem."%'")
            ->queryAll();

        return $Res;
    }
}