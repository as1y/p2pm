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


      //  echo "<h2><font color='#8b0000'>СВЯЗКИ USDT-EXCHANGE-USDT</font></h2>";
        $Method = "DOGE";

        $StartCapintal = 14000;
        $exchange = "Poloniex";

        $this->FC = $this->GetFC($exchange);

       // show($this->FC);


        echo "<h2>VER 3.0</h2>";

        // Получаем базовые заходы монет
        //  ЩАГ-1 МОНЕТЫ ВХОДА

        $StartArr = $this->GetStartArr($Method, $StartCapintal);


        // СИТО ЧЕРЕЗ ТОРГОВЛЮ
        $ArrPER[] = "USDT";
        $ArrPER[] = "BTC";
        $ArrPER[] = "ETH";
        $ArrPER[] = "TRX";


        echo "<h2>".$exchange."</h2>";


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


    private function GetFC($exchange){

        $DATA = [];

        if ($exchange == "Poloniex")
        {
            $exchange = new \ccxt\poloniex (array ('timeout' => 30000));
            $DATA = $exchange->fetchCurrencies();
        }

        if ($exchange == "Hitbtc")
        {
            $exchange = new \ccxt\hitbtc (array ('timeout' => 30000));
            $DATA = $exchange->fetchCurrencies();
        }


        if ($exchange == "Huobi")
        {

            $exchange = new \ccxt\huobipro (array ('timeout' => 30000));
            $DATA = $exchange->fetchCurrencies();
        }



        if ($exchange == "Binance")
        {

            $exchange = new \ccxt\binance (array(
                'apiKey' => json_decode($_SESSION['ulogin']['requis'], true)['apiBinance'],
                'secret' => json_decode($_SESSION['ulogin']['requis'], true)['keyBinance'],
                'timeout' => 30000,
                'enableRateLimit' => true,
                'options' => array(
                    'fetchCurrencies' => true

                )
            ));

            $DATA = $exchange->fetchCurrencies();
        }


        if ($exchange == "Gateio")
        {
            $exchange = new \ccxt\gateio (array ('timeout' => 30000));
            $DATA = $exchange->fetchCurrencies();
        }




        if (empty($DATA))
        {
            echo "<font color='red'>Ошибка загрузки статуса монет!</font>";
        }

        return $DATA;

    }


    private function Sito1($WorkArr, $exchange, $Perekrestok){

        $DATA = [];

        $ExchangeTickers = $this->GetTickerText($exchange);


        // Берем монету BTC -> МЕНЯЕМ НА БНБ -> НА БНБ покупаем остальные монеты

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
            $STEP1[$key] = $amountPerekrestok;
        }
        arsort($STEP1);

        $DATA['symbol'] = array_key_first($STEP1);
        $DATA['perekrestok'] = $Perekrestok;
        $DATA['amount'] = reset($STEP1);

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



        if ($this->FC[$symbol]['id'] == $symbol)
        {
            if (!empty($this->FC[$symbol]['info']['disabled']) && $this->FC[$symbol]['info']['disabled'] == 1) return false;

        }





        return true;

    }


    private function GetStartArr($Method, $StartCapintal){

        $DATA = [];

        $WORk = $this->LoadTickersBD("IN", $Method);
        foreach ($WORk as $VAL)
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