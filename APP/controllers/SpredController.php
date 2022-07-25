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

    // ТЕХНИЧЕСКИЕ ПЕРЕМЕННЫЕ
    public function indexAction()
    {

        $this->layaout = false;

      //  date_default_timezone_set('UTC');
        // Браузерная часть


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


        // Входные данные

     //   $this->EXCHANGES[] = "Binance";
//        $this->EXCHANGES[] = "Poloniex";
 //       $this->EXCHANGES[] = "Gateio";
 //       $this->EXCHANGES[] = "Huobi";
   //     $this->EXCHANGES[] = "Ftx";
   //     $this->EXCHANGES[] = "Exmo";
  //      $this->EXCHANGES[] = "Kucoin";
  //      $this->EXCHANGES[] = "Okex";



        $this->StartMoneta = "USDT";
        $this->StartCapital = 5000;

        $exchange = "Poloniex";


        $DATA = $this->GetWorkARR($exchange);

        show($DATA);

        // Вводные данные:
        // USDT, Биржа, Монета
        exit("111");


        // СИТО ЧЕРЕЗ ТОРГОВЛЮ
        $ArrPER[] = "USDT";
        $ArrPER[] = "BTC";
        $ArrPER[] = "ETH";
        $ArrPER[] = "TRX";



        foreach ($ArrPER as $vl)
        {

           // echo $vl."<br>";
            $STEP1 = $this->Sito1($StartArr, $exchange, $vl);

        //    show($STEP1);

            echo "Отдаем ".$StartCapintal."  ".$Method." -> Покупаем ".$STEP1['symbol']." ".$STEP1['sito'][$STEP1['symbol']]." на биржу ".$exchange."  <br> ";
            echo "Меняем ".$STEP1['symbol']." на ".$STEP1['perekrestok']." : ".$STEP1['amount']."  <br> ";


          //  echo "Монеты после СИТА<br>";
            $EndArr = $this->GetEndArr($STEP1['sito'], $Method, $StartCapintal);

            $endmoneta = array_key_first($EndArr);
            $endamount = reset($EndArr);

            echo "Меняем ".$STEP1['perekrestok']." на ".$endmoneta." |  Далее ".$endmoneta." продаем через обменник ".$endamount." <br> ";


             show($EndArr);


        }



//        echo "Монеты ДО СИТА<br>";
//        $EndArr = $this->GetEndArr($StartArr, $Method);
//        show($EndArr);




        echo "<hr>";



//        $this->set(compact(''));

    }




    private function GetWorkARR($exchange){
        $DATA = [];

        // Загрузка данных
        $TickersIN = $this->LoadTickersBD("IN", $this->StartMoneta);
        $TickersOUT = $this->LoadTickersBD("OUT", $this->StartMoneta);
        $ExchangeTickers = $this->GetTickerText($exchange);


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



        // ШАГ -1 БАЗОВЫЙ ВХОД НА МОНЕТУ ИЗ ВСЕХ БИРЖ
        $StartArr = $this->GetStartArr($TickersIN, $this->StartCapital);


        $Perekrestok = "BTC";

        echo "Получаем сколько можем получить ".$Perekrestok." если продадим купленную монету";

        $SITO = $this->SitoStep1($StartArr, $Perekrestok, $ExchangeTickers);

        show($SITO);


        exit("1111");


        return $DATA;

    }




    private function SitoStep1($WorkArr, $Perekrestok,$ExchangeTickers){

        $DATA = [];
        $STEP1 = [];
        // ШАГ-1 Получаем самый выгодный курс переход в монету перекрестка

        foreach ($WorkArr as $key=>$value)
        {

            // Получаем ТИКЕР с БИРЖИ
            $TickerBirga = $key."/".$Perekrestok."";
            if (empty($ExchangeTickers[$TickerBirga]['bid'])) continue;
            //  show($ExchangeTickers[$TickerBirga]);
            //       echo "Тикер на бирже: ".$TickerBirga." <br>";
            $avgprice = ($ExchangeTickers[$TickerBirga]['bid']+$ExchangeTickers[$TickerBirga]['ask'])/2;
            $amountPerekrestok = $value*$avgprice;
            // echo "Берем монету ".$key." меняем ее на ".$Perekrestok." и получаем ".$amountPerekrestok." ".$Perekrestok." <br> ";
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

        show($STEP1);

        $DATA['startcapital'] = $this->StartCapital;
        $DATA['startmoneta'] = $this->StartMoneta;
        $DATA['symbolbest'] = array_key_first($STEP1['result']);
        $DATA['symbolamount'] = $STEP1['amount'][$DATA['symbolbest']];
        $DATA['perekrestok'] = $Perekrestok;
        $DATA['amount'] = reset($STEP1['result']);

        return $DATA;

    }


    private function Sito1($WorkArr, $Perekrestok, $ExchangeTickers){




        // ШАГ2 Получаем кол-во монет, которые сможем купить за монету перекрестка

        foreach ($WorkArr as $key=>$value){

            $TickerBirga = $key."/".$Perekrestok."";
            if (empty($ExchangeTickers[$TickerBirga]['bid'])) continue;

            $avgprice = ($ExchangeTickers[$TickerBirga]['bid']+$ExchangeTickers[$TickerBirga]['ask'])/2;
            $amoumtMoneta = $DATA['amount']/$avgprice;

          //  echo "Работаем с тикером ".$key."<br>";
          //  echo "Тикер на бирже ".$TickerBirga."<br>";
         //   echo "Цена ".$avgprice."<br>";
         //   echo "Кол-во актива ".$amoumtMoneta."<br>";
            $DATA['sito'][$key] = $amoumtMoneta;

        }



        return $DATA;
    }



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



    private function GetStartArr($TickersIN, $StartCapintal){

        $DATA = [];

        foreach ($TickersIN as $VAL)
        {

            // Проверка на доступностью тикера на покупку в бирже
            $checksymbol = $this->checksymbolenter($VAL['ticker']);

            if ($checksymbol == false)
            {
                echo "<font color='red'>Тикер ".$VAL['ticker']." отключен  </font> <br>";
                continue;

            }

            if ($VAL['limit'] > $StartCapintal){
                echo "<font color='red'>Тикер ".$VAL['ticker']." не проходит по стартовому капиталу  </font> <br>";
                continue;
            }



            $DATA[$VAL['ticker']] = $StartCapintal/$VAL['price'];
        }



        return $DATA;
    }

    private function GetEndArr($WorkArr, $Method, $StartCapintal){

        $DATA = [];

        $WORk = $this->LoadTickersBD("OUT", $Method);

        foreach ($WORk as $VAL){

           // echo "Цена выхода: ".$VAL['price']."<br>";
            if (empty($WorkArr[$VAL['ticker']])) continue;


            if ($VAL['limit'] > $StartCapintal){
                echo "<font color='red'>Тикер ".$VAL['ticker']." не проходит по стартовому капиталу  </font> <br>";
                continue;
            }

            $DATA[$VAL['ticker']] = $WorkArr[$VAL['ticker']]*$VAL['price'];

        }

        arsort($DATA);

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