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

    private $BaseKurs = 0;

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

        $oborot = 10000;

        echo "<h2> 2 УРОВНЯ BTC </h2>";
        echo "Объем: ".$oborot."<br>";

        $RENDER['BestPrice'] = 0;


        foreach ($TickersBDIN as $TickerWork)
        {
            if ($TickerWork['price'] == "none") continue;

            $RENDER =  $this->RenderPercent($RENDER, $TickerWork);

            echo "<b>СИМВОЛ:</b> ".$RENDER['Symbol']." <br>";
            echo "Лучшая цена ".$RENDER['BestPrice']."<br>";
        //    echo "Цена BestChange ".$RENDER['ObmenPrice']."<br>";
        //    echo "<b> СПРЕД ВХОДА </b> ".$RENDER['Spred']." % <br>";
            echo "<hr>";


        }


        show($RENDER['BestSpredSymbol']);
        show($RENDER['BestPrice']);












//        $this->set(compact(''));

    }





    private function RenderPercent($RENDER, $TickerWork)
    {

        $symbolBTC = $TickerWork['ticker']."/BTC";

        $RENDER['Symbol'] = $symbolBTC;


        if ($symbolBTC == "BTC/BTC")
        {
            $BinancePRICE = 1;
            $BtcConvertPrice = $TickerWork['price'];
        }

        if ($symbolBTC != "BTC/BTC")
        {
            $BinancePRICE = $this->TickerBinance[$symbolBTC]['close'];
            $BtcConvertPrice = $TickerWork['price']/$BinancePRICE;

        }


        echo "За 1 единицу: ".$TickerWork['ticker']." - цена в обменнике: ".$TickerWork['price']." -  по итогу получаем:  ".$BinancePRICE." BTC == ".$BtcConvertPrice." <br>";



        if ($RENDER['BestPrice'] == 0)
        {
            $RENDER['BestPrice'] = $BtcConvertPrice;
            $RENDER['BestSpredSymbol'] = $symbolBTC;
        //    return $RENDER;
        }


        if ($RENDER['BestPrice'] > $BtcConvertPrice) {
       //     echo "Меняем".$RENDER['BestPrice']." на ".$BtcConvertPrice."<br>";
            $RENDER['BestPrice'] = $BtcConvertPrice;
            $RENDER['BestSpredSymbol'] = $symbolBTC;

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