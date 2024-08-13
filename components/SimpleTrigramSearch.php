<?php

namespace app\components;

use Yii;
use yii\base\Component;
use app\models\BookcatalogModel;

class SimpleTrigramSearch extends Component
{
    /**
     * Класс триграммного поиска
     */

    public $bookModel;

    public function __construct()
    {
        $this->bookModel = new BookcatalogModel();
    }

    public function search($searchWord)
    {
        /**
         * Ищем триграммы в БД. Возвращаем массив с ID книг.
         *
         * @param string $searchWord
         * @return array
         */

        $res= [];
        $trigrams = self::generateTrigrams($searchWord);
        $triCounter = [];

        // Идём по триграммам
        foreach($trigrams as $triWord) {
            $res = $this->bookModel->BookSearch( $triWord );

            foreach($res as $resData) {

                if( !isset($triCounter[$resData['book_id']]) ) {
                    $triCounter[$resData['book_id']] = 0;
                }
    
                // Фиксируем попадания
                $triCounter[$resData['book_id']] += 1;
            }
        }

        $Out = [];

        // Идём по найденным попаданиям
        foreach($triCounter as $bookId => $input) {

            // Вычисляем коэффицент правильности совпадения
            $len = floor(strlen($searchWord) / 2);
            $prec = round(($len / $input), 2);

            // Если коэф соответствует, то разрешаем к выдаче. Коэффицент должен быть не более 1.4. 
            if( $prec <= 1.3 ) {

                $Out[] = $bookId;
            }
        }

        return $Out;
    }

    private function generateTrigrams($word)
    {
        /**
         * Делим входящую строку на триграммы
         * 
         * @param string $word
         * @return array
         */

        $trigrams = [];

        for ($i = 0; $i < strlen($word) - 2; $i++) {

            if( !empty(mb_substr($word, $i, 3)) && strlen( mb_substr($word, $i, 3)) > 3 ) {
                $trigrams[] = mb_substr($word, $i, 3);
            }
        }
        return $trigrams;
    }
}