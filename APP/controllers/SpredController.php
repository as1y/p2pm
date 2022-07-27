<?php
namespace APP\controllers;
use APP\core\Cache;
use APP\models\Addp;
use APP\models\Panel;
use APP\core\base\Model;
use RedBeanPHP\R;

class SpredController extends AppController {
    public $layaout = 'PANEL';
    public $BreadcrumbsControllerLabel = "Панель управления";
    public $BreadcrumbsControllerUrl = "/panel";

    public $TickersBDIN = [];
    public $TickersBDOUT = [];
    public $EXCHANGES = [];
    public $minumumspred = 0.3;

    public $FC = [];
    public $StartCapital = 0;
    public $StartMoneta = "";
    public $PereWork = [];


    // ТЕХНИЧЕСКИЕ ПЕРЕМЕННЫЕ
    public function indexAction()
    {

        $this->layaout = false;


        $Panel =  new Panel();
        $META = [
            'title' => 'Панель BURAN',
            'description' => 'Панель BURAN',
            'keywords' => 'Панель BURAN',
        ];
        $BREADCRUMBS['HOME'] = ['Label' => $this->BreadcrumbsControllerLabel, 'Url' => $this->BreadcrumbsControllerUrl];
        $BREADCRUMBS['DATA'][] = ['Label' => "FAQ"];
        \APP\core\base\View::setBreadcrumbs($BREADCRUMBS);
        $ASSETS[] = ["js" => "/global_assets/js/plugins/tables/datatables/datatables.min.js"];
        $ASSETS[] = ["js" => "/assets/js/datatables_basic.js"];
        \APP\core\base\View::setAssets($ASSETS);
        \APP\core\base\View::setMeta($META);
        \APP\core\base\View::setBreadcrumbs($BREADCRUMBS);


        // С какими перекрестками работаем
        $this->PereWork[] = "USDT";
        $this->PereWork[] = "BTC";
        $this->PereWork[] = "ETH";
        $this->PereWork[] = "TRX";



        $this->StartMoneta = "USDT";
        $this->StartCapital = 1000;

        $exchange = "Poloniex";



        $DATA = $this->GetWorkARR($exchange);

        show($DATA);

        // Вводные данные:
        // USDT, Биржа, Монета







//        $this->set(compact(''));

    }




    private function GetWorkARR($exchange){
        $DATA = [];

        // Загрузка данных
        $TickersIN = $this->LoadTickersBD("IN", $this->StartMoneta);
        $TickersOUT = $this->LoadTickersBD("OUT", $this->StartMoneta);
        $ExchangeTickers = $this->GetTickerText($exchange);

       // show($ExchangeTickers);
       // exit("11");

        // Проверка параметров

        if (empty($TickersIN))
        {
            $DATA['errors'] = "Монета ".$this->StartMoneta." не поддерживается<br>";
            return $DATA;
        }

        if (!is_numeric($this->StartCapital)){
            $DATA['errors'] = "Не корректно задан рабочий капитал<br>";
            return $DATA;
        }

        // Получение положения монет
       $this->FC = $this->GetCurText($exchange);
        if (empty($this->FC)){
            $DATA['errors'] = "Ошибка загрузки монет<br>";
            return $DATA;
        }



        foreach ($this->PereWork as $Perekrestok)
        {
            // ШАГ -1 БАЗОВЫЙ ВХОД НА МОНЕТУ ИЗ ВСЕХ БИРЖ
            //  echo "Получаем сколько можем получить ".$Perekrestok." если продадим купленную монету <br>";
            $StartArr = $this->GetStartArr($TickersIN);

            $SITO1 = $this->SitoStep1($StartArr, $Perekrestok, $ExchangeTickers);

            //show($SITO1);

            $EndArr[$exchange][$Perekrestok]['info'] = $SITO1;
            //show($SITO1);
            // echo "Получаем список монет которые сможем купить за ".$SITO1['amount']." - ".$SITO1['perekrestok']." <br>";
            $SITO2 = $this->SitoStep2($TickersOUT, $SITO1, $ExchangeTickers);
            //show($SITO2);
            $EndArr[$exchange][$Perekrestok]['result'] = $this->GetEndArr($SITO2, $TickersOUT);

        }



        show($EndArr);




        return $DATA;

    }



    private function GetStartArr($TickersIN){

        $DATA = [];

        foreach ($TickersIN as $VAL)
        {

            // Проверка на доступностью тикера на покупку в бирже
            $checksymbol = $this->checksymbolenter($VAL['ticker']);

            if ($checksymbol == false)
            {
                // echo "<font color='red'>Тикер ".$VAL['ticker']." отключен  </font> <br>";
                continue;
            }


            if ($VAL['limit'] > $this->StartCapital){
                //  echo "<font color='red'>Тикер ".$VAL['ticker']." не проходит по стартовому капиталу  </font> <br>";
                continue;
            }


            $DATA[$VAL['ticker']] = $this->StartCapital/$VAL['price'];
        }



        return $DATA;
    }

    private function SitoStep1($StartArr, $Perekrestok,$ExchangeTickers){

        $DATA = [];
        $STEP1 = [];
        // ШАГ-1 Получаем самый выгодный курс переход в монету перекрестка

        foreach ($StartArr as $key=>$value)
        {

            // Получаем ТИКЕР с БИРЖИ
            $TickerBirga = $key."/".$Perekrestok."";
            if (empty($ExchangeTickers[$TickerBirga]['bid'])) continue;
           //   show($ExchangeTickers[$TickerBirga]);
            //       echo "Тикер на бирже: ".$TickerBirga." <br>";
            $avgprice = ($ExchangeTickers[$TickerBirga]['bid']+$ExchangeTickers[$TickerBirga]['ask'])/2;
            $amountPerekrestok = $value*$avgprice;
            // echo "Берем монету ".$key." меняем ее на ".$Perekrestok." и получаем ".$amountPerekrestok." ".$Perekrestok." <br> ";

            // Фильтрация символ на ОБЪЕМ ТОРГОВ
           // echo "Объем торгов монетой: ".$TickerBirga." - ".$ExchangeTickers[$TickerBirga]['baseVolume']." <br>";
           // echo "Наше кол-во монеты: ".$value."<br>";

            if ($value > $ExchangeTickers[$TickerBirga]['baseVolume']/2)
            {
                //echo "<font color='red'>Тикер ".$TickerBirga." не проходит по объему торгов</font> <br> ";
                continue;
            }

            $STEP1['amount'][$key] = $value;
            $STEP1['result'][$key] = $amountPerekrestok;

        }

        if (empty($STEP1))
        {
            $DATA['errors'] = "Ошибка 101 (".$Perekrestok.")";
            //  echo "<font color='#8b0000'>На бирже отсутсвует перекидывание через <b>".$Perekrestok."</b></font>";
            return $DATA;
        }

        arsort($STEP1['result']);

        //show($STEP1);

        $DATA['startcapital'] = $this->StartCapital;
        $DATA['startmoneta'] = $this->StartMoneta;
        $DATA['symbolbest'] = array_key_first($STEP1['result']);
        $DATA['symbolamount'] = $STEP1['amount'][$DATA['symbolbest']];
        $DATA['perekrestok'] = $Perekrestok;
        $DATA['amount'] = reset($STEP1['result']);

        if ($this->StartMoneta == $Perekrestok)
        {
            $DATA['amountstart'] = reset($STEP1['result']);
        }

        if ($this->StartMoneta != $Perekrestok)
        {
            $pricetick = $Perekrestok."/".$this->StartMoneta;
            $avgprice = ($ExchangeTickers[$pricetick]['bid']+$ExchangeTickers[$pricetick]['ask'])/2;
            $DATA['amountstart'] = $DATA['amount']*$avgprice;
        }
 

        // Добавление кол-во итоговой монеты в монете входа


        return $DATA;

    }

    private function SitoStep2($TickersOUT, $SITO1, $ExchangeTickers){

        $DATA = [];

        // ШАГ2 Получаем кол-во монет, которые сможем купить за монету перекрестка

        // Проверка на доступностью тикера на покупку в бирже

        foreach ($TickersOUT as $VAL){

            $checksymbol = $this->checksymbolenter($VAL['ticker']);

            if ($checksymbol == false)
            {
              //  echo "<font color='red'>Тикер ".$VAL['ticker']." отключен  </font> <br>";
                continue;
            }
            if ($VAL['limit'] > $this->StartCapital){
              //  echo "<font color='red'>Тикер ".$VAL['ticker']." не проходит по стартовому капиталу  </font> <br>";
                continue;
            }

            if (empty($SITO1['perekrestok'])) continue;

            $TickerBirga = $VAL['ticker']."/".$SITO1['perekrestok']."";

            if (empty($ExchangeTickers[$TickerBirga]['bid'])) continue;

            $avgprice = ($ExchangeTickers[$TickerBirga]['bid']+$ExchangeTickers[$TickerBirga]['ask'])/2;
            $amoumtMoneta = $SITO1['amount']/$avgprice;


            // Фильтрация символ на ОБЪЕМ ТОРГОВ
            // echo "Объем торгов монетой: ".$TickerBirga." - ".$ExchangeTickers[$TickerBirga]['baseVolume']." <br>";
            // echo "Наше кол-во монеты: ".$amoumtMoneta."<br>";

            if ($amoumtMoneta > $ExchangeTickers[$TickerBirga]['baseVolume']/2)
            {
              //  echo "<font color='red'>Тикер ".$TickerBirga." не проходит по объему торгов</font> <br> ";
                continue;
            }


            //  echo "Работаем с тикером ".$VAL['ticker']."<br>";
           //   echo "Тикер на бирже ".$TickerBirga."<br>";
          //     echo "Цена ".$avgprice."<br>";
          //     echo "Кол-во актива ".$amoumtMoneta."<br>";
            $DATA[$VAL['ticker']] = $amoumtMoneta;

        }

        return $DATA;


    }

    private function GetEndArr($SITO2, $TickersOUT){

        $DATA = [];

        //show($SITO2);

        foreach ($TickersOUT as $VAL){

           // echo "Цена выхода: ".$VAL['price']."<br>";
            if (empty($SITO2[$VAL['ticker']])) continue;

          //  echo "Продаем ".$VAL['ticker']." по цене ".$VAL['price']."  <br>";

            $DATA[$VAL['ticker']] = $SITO2[$VAL['ticker']]*$VAL['price'];

        }

        arsort($DATA);

        return $DATA;

    }



    // ВСПОМОГАЮЩИЕ ФУНКЦИИ


    private function checksymbolenter($symbol)
    {

        if ($symbol == "USDT") return true;
        if ($symbol == "BTC") return true;
        if ($symbol == "ETH") return true;


        if (empty($this->FC[$symbol]))
        {
            //echo "Символа ".$symbol." нет на бирже! <br>";
            return false;
        }


        if (isset($this->FC[$symbol]['payin']) && $this->FC[$symbol]['payin'] == false) return false;
        if (isset($this->FC[$symbol]['payout']) && $this->FC[$symbol]['payout'] == false) return false;



        if ($this->FC[$symbol]['code'] == $symbol)
        {
            if (!empty($this->FC[$symbol]['info']['disabled']) && $this->FC[$symbol]['info']['disabled'] == 1) return false;

        }





        return true;

    }

    private function GetCurText($exchange){

        $DATA = [];

        $file = file_get_contents(WWW."/Cur".$exchange.".txt");     // Открыть файл data.json
        $DATA = json_decode($file,TRUE);              // Декодировать в массив


        if (empty($DATA)) return false;

        return $DATA;

    }

    private function LoadTickersBD($type, $method)
    {

        $table = [];
        if ($type == "IN") $table = R::findAll("obmenin", 'WHERE method=?', [$method]);
        if ($type == "OUT") $table = R::findAll("obmenout",'WHERE method=?', [$method]);

        return $table;
    }

    private function GetTickerText($exchange){

        $file = file_get_contents(WWW."/Ticker".$exchange.".txt");     // Открыть файл data.json
        $MASSIV = json_decode($file,TRUE);              // Декодировать в массив
        return $MASSIV;

    }






}
?>