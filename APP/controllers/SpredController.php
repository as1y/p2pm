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

    public $ApiKey = "U5I2AoIrTk4gBR7XLB";
    public $SecretKey = "HUfZrWiVqUlLM65Ba8TXvQvC68kn1AabMDgE";


    private $TickerBinance =[];

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
        $exchange = new \ccxt\binance (array (
          //  'verbose' => true,
            'timeout' => 30000,
        ));

 //       $this->BaseKurs = $exchange->fetch_ticker ("USDT/RUB")['close'];
        $this->TickerBinance = $exchange->fetch_tickers();
        $TickersBDIN = $this->LoadTickersBD("IN");



        echo "<h2>БАЗОВЫЕ ПАРАМЕТРЫ ЗАХОДА</h2>";


        //show($this->TickerBinance);
    //    exit("111");



     // show($TickersBDIN);

        $priceBTC =  $this->GetPriceAct("BTC");
        $priceETH =  $this->GetPriceAct("ETH");
        $priceUSDT =  $this->GetPriceAct("USDT");


         echo "<b>Стартовая цена захода BTC: </b>".$priceBTC."<br>";
         echo "<b>Стартовая цена захода ETH: </b>".$priceETH."<br>";
         echo "<b>Стартовая цена захода USDT: </b>".$priceUSDT."<br>";





        // Проверяем лучшую цену через заход в BTC
        echo "<h3>ВХОД ЧЕРЕЗ МОНЕТЫ - BTC</h3>";
        $RENDER = $this->CheckBestPrice("BTC", $TickersBDIN);
        echo "<b>Самый выгодный символ:</b> ".$RENDER['BestSpredSymbol']." <br>";
        echo "Лучшая цена ".$RENDER['BestPrice']."<br>";


        echo "<h3>ВХОД ЧЕРЕЗ МОНЕТЫ - ETH</h3>";
        $RENDER = $this->CheckBestPrice("ETH", $TickersBDIN);
        echo "<b>Самый выгодный символ:</b> ".$RENDER['BestSpredSymbol']." <br>";
        echo "Лучшая цена ".$RENDER['BestPrice']."<br>";



        echo "<h3>ВХОД ЧЕРЕЗ МОНЕТЫ - USDT</h3>";
        $RENDER = $this->CheckBestPrice("USDT", $TickersBDIN);
        echo "<b>Самый выгодный символ:</b> ".$RENDER['BestSpredSymbol']." <br>";
        echo "Лучшая цена ".$RENDER['BestPrice']."<br>";





//        $this->set(compact(''));

    }





    private function CheckBestPrice($MONETA, $TICKERS){

        $RENDER['BestPrice'] = 0;
        $RENDER['BestSpredSymbol'] = "";

        foreach ($TICKERS as $TickerWork)
        {

            if ($TickerWork['price'] == "none") continue;
            if ($TickerWork['ticker'] == $MONETA) continue;


            // Проверяемый тикер
            $ExchangeTicker = $TickerWork['ticker']."/".$MONETA."";
            // Цена на бирже ЭТОЙ монеты
            $ExPRICE = $this->TickerBinance[$ExchangeTicker]['close'];
            if (empty($ExPRICE)) continue;



            // КОНТРОЛЬ ПЕРЕКРЕСТНЫХ ТИКЕРОВ
            if ($ExchangeTicker == "USDT/BTC"){
                $ExchangeTicker = "BTC/USDT";
                $ExPRICE = $this->TickerBinance[$ExchangeTicker]['close'];
                $ExPRICE = 1/$ExPRICE;
            }



            if (empty($ExPRICE)) continue;

        //    echo "Монета :".$MONETA."<br>";
         //   echo "Тикер на бирже :".$ExchangeTicker."<br>";
         //   echo "Цена на монеты :".$ExPRICE."<br>";
         //   echo "Сколько получим BTC :".$BtcConvertPrice."<br>";

            $RENDER =  $this->RenderPercent($RENDER, $TickerWork, $ExchangeTicker, $ExPRICE, $MONETA);



        }


        echo "<hr>";

        return $RENDER;


    }



    private function GetPriceAct($MONETA){
        $zapis = R::findOne("basetickers", 'WHERE global =? AND ticker=?', ["QIWI", $MONETA]);
        return $zapis['price'];

    }




    private function RenderPercent($RENDER, $TickerWork, $ExchangeTicker, $ExPRICE, $MONETA)
    {


        $RENDER['Symbol'] = $ExchangeTicker;

        $BtcConvertPrice = $TickerWork['price']/$ExPRICE;
        $BtcConvertPrice = round($BtcConvertPrice, 2);

     //   $countMoneta = $ExPRICE;
    //    $countMoneta = round($countMoneta,20);

        echo "<b>1.</b> Покупаем в обменнике: <b>".$TickerWork['ticker']."</b> по цене  ".$TickerWork['price']." и зачисляем на кошелек биржи <br>";
        echo "<b>2.</b> На бирже меняем: <b>".$TickerWork['ticker']."</b> &#10144;   <b>".$MONETA."</b>  <br>";
        echo "<b>3</b> Получаем кол-во  ".$MONETA." | Это кол-во будет равно закупки по курсу  <b>".$MONETA."</b> = ".$BtcConvertPrice." ";


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