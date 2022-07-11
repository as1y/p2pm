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

//    public $ApiKey = "U5I2AoIrTk4gBR7XLB";
//    public $SecretKey = "HUfZrWiVqUlLM65Ba8TXvQvC68kn1AabMDgE";


    // ТЕХНИЧЕСКИЕ ПЕРЕМЕННЫЕ
    public function indexAction()
    {

        $this->layaout = false;

        date_default_timezone_set('UTC');
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
        // Браузерная часть

        //  show(\ccxt\Exchange::$exchanges); // print a list of all available exchange classes

        //Запуск CCXT
        $exchangeBinance = new \ccxt\binance (array (
          //  'verbose' => true,
            'timeout' => 30000,
        ));

        $exchangeByBit = new \ccxt\bybit (array (
            //  'verbose' => true,
            'timeout' => 30000,
        ));

        $exchangeHuobi = new \ccxt\huobipro (array (
            //  'verbose' => true,
            'timeout' => 30000,
        ));

        $exchangeGate = new \ccxt\gateio (array (
            //  'verbose' => true,
            'timeout' => 30000,
        ));

        $exchangePolonex = new \ccxt\poloniex (array (
            //  'verbose' => true,
            'timeout' => 30000,
        ));




        echo "<h2>БАЗОВЫЕ ПАРАМЕТРЫ ЗАХОДА</h2>";
        $STARTPRICE['BTC'] = $this->GetPriceAct("BTC");
        $STARTPRICE['ETH'] = $this->GetPriceAct("ETH");
        $STARTPRICE['USDT'] = $this->GetPriceAct("USDT");


        echo "<b>Стартовая цена захода BTC: </b>".$STARTPRICE['BTC']."<br>";
        echo "<b>Стартовая цена захода ETH: </b>".$STARTPRICE['ETH']."<br>";
        echo "<b>Стартовая цена захода USDT: </b>".$STARTPRICE['USDT']."<br>";

        echo "<hr>";

        $TickersBDIN = $this->LoadTickersBD("IN");



        // ЗАГРУЗКА ТИКЕРОВ БИРЖ

       // $this->TickerBinance = $exchangeBinance->fetch_tickers();
     //   $this->TickerByBit = $exchangeByBit->fetch_tickers();
      //  show($this->TickerByBit);

           $ALLHuobi = $exchangeHuobi->fetch_tickers();

            $AllGate = $exchangeGate->fetch_tickers();

           $AllPolonex = $exchangePolonex->fetch_tickers();


        echo "<h3>ВХОД ЧЕРЕЗ МОНЕТЫ - BTC (Poloniex)</h3>";
        $RENDER = $this->CheckBestPrice("BTC", "Poloniex", $TickersBDIN, $STARTPRICE, $AllPolonex);
        echo "<b>Самый выгодный символ:</b> ".$RENDER['BestSpredSymbol']." <br>";
        echo "Лучшая цена ".$RENDER['BestPrice']."<br>";

        echo "<h3>ВХОД ЧЕРЕЗ МОНЕТЫ - USDT (Poloniex)</h3>";
        $RENDER = $this->CheckBestPrice("USDT","Poloniex", $TickersBDIN, $STARTPRICE, $AllPolonex);
        echo "<b>Самый выгодный символ:</b> ".$RENDER['BestSpredSymbol']." <br>";
        echo "Лучшая цена ".$RENDER['BestPrice']."<br>";



        echo "<h3>ВХОД ЧЕРЕЗ МОНЕТЫ - USDT (GATE.IO)</h3>";
        $RENDER = $this->CheckBestPrice("USDT","GATE.IO", $TickersBDIN, $STARTPRICE, $AllGate);
        echo "<b>Самый выгодный символ:</b> ".$RENDER['BestSpredSymbol']." <br>";
        echo "Лучшая цена ".$RENDER['BestPrice']."<br>";

        echo "<h3>ВХОД ЧЕРЕЗ МОНЕТЫ - ETH (GATE.IO)</h3>";
        $RENDER = $this->CheckBestPrice("ETH","GATE.IO", $TickersBDIN, $STARTPRICE, $AllGate);
        echo "<b>Самый выгодный символ:</b> ".$RENDER['BestSpredSymbol']." <br>";
        echo "Лучшая цена ".$RENDER['BestPrice']."<br>";




        echo "<h3>ВХОД ЧЕРЕЗ МОНЕТЫ - BTC (HUOBI)</h3>";
        $RENDER = $this->CheckBestPrice("BTC","HUOBI", $TickersBDIN, $STARTPRICE, $ALLHuobi);
        echo "<b>Самый выгодный символ:</b> ".$RENDER['BestSpredSymbol']." <br>";
        echo "Лучшая цена ".$RENDER['BestPrice']."<br>";


        echo "<h3>ВХОД ЧЕРЕЗ МОНЕТЫ - ETH (HUOBI)</h3>";
        $RENDER = $this->CheckBestPrice("ETH", "HUOBI",$TickersBDIN, $STARTPRICE, $ALLHuobi);
        echo "<b>Самый выгодный символ:</b> ".$RENDER['BestSpredSymbol']." <br>";
        echo "Лучшая цена ".$RENDER['BestPrice']."<br>";


        echo "<h3>ВХОД ЧЕРЕЗ МОНЕТЫ - USDT (HUOBI)</h3>";
        $RENDER = $this->CheckBestPrice("USDT", "HUOBI",$TickersBDIN, $STARTPRICE, $ALLHuobi);
        echo "<b>Самый выгодный символ:</b> ".$RENDER['BestSpredSymbol']." <br>";
        echo "Лучшая цена ".$RENDER['BestPrice']."<br>";

        // ЗАГРУЗКА ТИКЕРОВ



/*
        echo "<h3>ВХОД ЧЕРЕЗ МОНЕТЫ - USDT (BYBIT)</h3>";
        $RENDER = $this->CheckBestPrice("USDT", $TickersBDIN, $STARTPRICE, $this->TickerByBit);
        echo "<b>Самый выгодный символ:</b> ".$RENDER['BestSpredSymbol']." <br>";
        echo "Лучшая цена ".$RENDER['BestPrice']."<br>";
*/



/*
        // Проверяем лучшую цену через заход в BTC
        echo "<h3>ВХОД ЧЕРЕЗ МОНЕТЫ - BTC</h3>";
        $RENDER = $this->CheckBestPrice("BTC", $TickersBDIN, $STARTPRICE);
        echo "<b>Самый выгодный символ:</b> ".$RENDER['BestSpredSymbol']." <br>";
        echo "Лучшая цена ".$RENDER['BestPrice']."<br>";

        echo "<h3>ВХОД ЧЕРЕЗ МОНЕТЫ - ETH</h3>";
        $RENDER = $this->CheckBestPrice("ETH", $TickersBDIN, $STARTPRICE);
        echo "<b>Самый выгодный символ:</b> ".$RENDER['BestSpredSymbol']." <br>";
        echo "Лучшая цена ".$RENDER['BestPrice']."<br>";


        echo "<h3>ВХОД ЧЕРЕЗ МОНЕТЫ - USDT</h3>";
        $RENDER = $this->CheckBestPrice("USDT", $TickersBDIN, $STARTPRICE);
        echo "<b>Самый выгодный символ:</b> ".$RENDER['BestSpredSymbol']." <br>";
        echo "Лучшая цена ".$RENDER['BestPrice']."<br>";
*/






//        $this->set(compact(''));

    }





    private function CheckBestPrice($MONETA, $ExchangeName , $TICKERS, $STARTPRICE, $ALLEXCHANGE){

        $RENDER['BestPrice'] = 0;
        $RENDER['BestSpredSymbol'] = "";

        foreach ($TICKERS as $TickerWork)
        {

            if ($TickerWork['price'] == "none") continue;
            if ($TickerWork['ticker'] == $MONETA) continue;


            // Проверяемый тикер
            $ExchangeTicker = $TickerWork['ticker']."/".$MONETA."";
            // Цена на бирже ЭТОЙ монеты


            // Перекрестные тикеры
            if ($MONETA == "BTC" && $ExchangeTicker == "USDT/BTC") $ExchangeTicker = "BTC/USDT";
            if ($MONETA == "ETH" && $ExchangeTicker == "BTC/ETH") $ExchangeTicker = "ETH/BTC";
            if ($MONETA == "ETH" && $ExchangeTicker == "USDT/ETH") $ExchangeTicker = "ETH/USDT";


            if (empty(($ALLEXCHANGE[$ExchangeTicker]['close']))) continue;

            $ExPRICE = $ALLEXCHANGE[$ExchangeTicker]['close'];

            // Перекрестные тикеры
            if ($MONETA == "BTC" && $ExchangeTicker == "BTC/USDT") $ExPRICE = 1 / $ExPRICE;
            if ($MONETA == "ETH" && $ExchangeTicker == "ETH/BTC") $ExPRICE = 1 / $ExPRICE;
            if ($MONETA == "ETH" && $ExchangeTicker == "ETH/USDT") $ExPRICE = 1 / $ExPRICE;




            if (empty($ExPRICE)) continue;

        //    echo "Монета :".$MONETA."<br>";
         //   echo "Тикер на бирже :".$ExchangeTicker."<br>";
         //   echo "Цена на монеты :".$ExPRICE."<br>";
         //   echo "Сколько получим BTC :".$BtcConvertPrice."<br>";

            $RENDER =  $this->RenderPercent($RENDER, $TickerWork, $ExchangeTicker, $ExPRICE, $MONETA, $STARTPRICE, $ExchangeName);



        }


        echo "<hr>";

        return $RENDER;


    }



    private function GetPriceAct($MONETA){
        $zapis = R::findOne("basetickers", 'WHERE global =? AND ticker=?', ["QIWI", $MONETA]);
        return $zapis['price'];

    }




    private function RenderPercent($RENDER, $TickerWork, $ExchangeTicker, $ExPRICE, $MONETA, $STARTPRICE, $ExchangeName)
    {


        $RENDER['Symbol'] = $ExchangeTicker;

        $BtcConvertPrice = $TickerWork['price']/$ExPRICE;
        $BtcConvertPrice = round($BtcConvertPrice, 2);

     //   $countMoneta = $ExPRICE;
    //    $countMoneta = round($countMoneta,20);

        $change = changemet($BtcConvertPrice, $STARTPRICE[$MONETA] );
        if ($change > 0) $change = "<font color='green'><b>".$change."</b></font>";
        if ($change <= 0) $change = "<font color='#8b0000'>".$change."</font>";


        echo "<b>1.</b> Покупаем в обменнике: <b>".$TickerWork['ticker']."</b> по цене  ".$TickerWork['price']." и зачисляем на кошелек биржи <b>".$ExchangeName."</b> <br>";
        echo "<b>2.</b> На бирже меняем: <b>".$TickerWork['ticker']."</b> &#10144;   <b>".$MONETA."</b>  <br>";
        echo "<b>3.</b> Получаем кол-во  ".$MONETA." | Это кол-во будет равно закупки по курсу  <b>".$MONETA."</b> = ".$BtcConvertPrice." <br> ";
        echo "<b>4.</b> СПРЕД ВХОДА: ".$change." % <br>" ;


        echo "<hr>";


      //  echo "Покупаем в обменнике: < b>".$TickerWork['ticker']."</b> по цене  ".$TickerWork['price']."  &#10144; переводим на биржу  ".$ExPRICE." BTC == ".$BtcConvertPrice." <br>";


        if ($RENDER['BestPrice'] == 0)
        {
            $RENDER['BestPrice'] = $BtcConvertPrice;
            $RENDER['BestSpredSymbol'] = $ExchangeTicker;
        //    return $RENDER;
        }


        if ($RENDER['BestPrice'] > $BtcConvertPrice) {
       //     echo "Меняем".$RENDER['BestPrice']." на ".$BtcConvertPrice."<br>";
            $RENDER['BestPrice'] = $BtcConvertPrice;
            $RENDER['BestSpredSymbol'] = $ExchangeTicker;

        }




        return $RENDER;


    }




    public function GetBal(){
        $balance = $this->EXCHANGECCXT->fetch_balance();
        return $balance;
    }


    private function GetTreksBD($side)
    {
        $terk = R::findAll($this->namebdex, 'WHERE emailex =? AND workside=?', [$this->emailex, $side]);
        return $terk;
    }


    private function LoadTickersBD($type)
    {
        $table = R::findAll("basetickers", 'WHERE type =?', [$type]);
        return $table;
    }







}
?>