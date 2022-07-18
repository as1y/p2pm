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

    public $ENTER = [];
    public $EXIT = [];


    public $minumumspred = 0.1;


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


        // Входные данные

        $this->EXCHANGES[] = "Binance";
        $this->EXCHANGES[] = "Poloniex";
        $this->EXCHANGES[] = "Gateio";
        $this->EXCHANGES[] = "Huobi";
   //     $this->EXCHANGES[] = "Ftx";
   //     $this->EXCHANGES[] = "Exmo";
  //      $this->EXCHANGES[] = "Kucoin";
  //      $this->EXCHANGES[] = "Okex";


        $ENTER[] = "QIWI";


        $EXIT[] = "VISA";




        echo "<h2><font color='#8b0000'>СВЯЗКИ USDT-EXCHANGE-USDT</font></h2>";
        $Base = "USDT";

        foreach ($this->EXCHANGES as $key=>$exchange)
        {

            echo "<h2>СКАН ".$exchange." </h2>";
            $MassivWork =  $this->GetArrWorkExchange($exchange, $Base);


            show($MassivWork);



           //  $this->RenderFinalExchange($MassivEnter, $exchange, "USDT");

        }


        echo "<hr>";






//        $this->set(compact(''));

    }



    private function GetArrWorkExchange($exchange, $base){

        $TickersBDIN = $this->LoadTickersBD("IN", $base);
        $TickersBDOUT = $this->LoadTickersBD("OUT", $base);

        $ExchangeTickers = $this->GetTickerText($exchange);

        $ArrEnter =  $this->GetArrEnter($TickersBDIN, $ExchangeTickers, $base);
        $Obrabotka['enter'] = $this->LoadObrabotka($ArrEnter, "enter", $exchange);

       // echo "<b>ТОЧКИ ВХОДА</b><br>";
      //  show($Obrabotka);


        $ArrExit =  $this->GetArrExit($TickersBDOUT, $ExchangeTickers, $base);
        $Obrabotka['exit'] = $this->LoadObrabotka($ArrExit, "exit", $exchange);





        return $Obrabotka;

    }


    private function RenderFinalExchange($MassivEX, $exname, $Method){



        //echo "<h2>".$exname."</h2>";


        foreach ($MassivEX as $key=>$val)
        {
            if ($val['finalspred'] < 0.1) continue;

            echo "<b>1.</b> На BestChange отдаем <b>".$Method."</b> получаем <b>".$val['moneta']."</b> . Вводим кошелек для зачисления биржи <b>".$exname."</b> <br>";

            echo "<b>2.</b> На бирже <b>".$exname."</b> монету <b>".$val['moneta']."</b>  меняем на <b>".$val['symbol']."</b> <br>";

            echo "<b>3.</b> На бирже <b>".$exname."</b> меняем <b>".$val['symbol']."</b>  на <b>".$val['exitmoneta']."</b> <br>";

            echo "<b>4.</b> Отдаем монету <b> ".$val['exitmoneta']."</b>  получаем <b>".$Method."</b> по лучшему курсу через BestChange <br>";


            echo "<b>5.</b> Зарабатываем <b> <font color='green'>".$val['finalspred']."% </font></b> с круга <br>";
            echo "<hr>";

        }





        return true;
    }



    private function GetArrExit($TickersBDOUT,$ExchangeTickers, $base ){

        $MASSIV = [];

        foreach ($TickersBDOUT as $ket=>$TickerWork)
        {

            if ($TickerWork['price'] == "none") continue;
            if ($TickerWork['ticker'] == $base) continue;
            if ($TickerWork['price'] == 0) continue;


            $TickerBirga = $TickerWork['ticker']."/".$base."";

            // ПРИ ДЕБАГЕ И РЕФАКТОРИНГЕ ТЕСТИТЬ И ПРОВЕРЯТЬ!!!!!
            if (empty(($ExchangeTickers[$TickerBirga]['close']))) continue;

            $ExPRICE = $ExchangeTickers[$TickerBirga]['close'];
            $change = changemet($ExPRICE, $TickerWork['price'] );


           //   echo "Работаем с <b> ".$TickerWork['ticker']."</b> <br>";
          //    echo "На бирже покупаем ".$TickerWork['ticker']." за ".$base." получаем  ".$TickerWork['ticker']." <br> ";
         //    echo "Цена покупки на бирже: ".$ExPRICE."<br>";
         //    echo "Цена продажи по обменникам: ".$TickerWork['price']."<br>";
         //    echo "Спред выхода: ".$change."<br>";

            $MASSIV['spred'][$TickerWork['ticker']] = $change;

            $MASSIV['enterprice'][$TickerWork['ticker']] = $ExPRICE;
            $MASSIV['exitprice'][$TickerWork['ticker']] = $TickerWork['price'];

            $MASSIV['url'][$TickerWork['ticker']] = $TickerWork['url'];
            $MASSIV['limit'][$TickerWork['ticker']] = $TickerWork['limit']*$TickerWork['price'];




        }

        arsort($MASSIV['spred']);


        return $MASSIV;


    }

    private function GetArrEnter($TickersBDIN,$ExchangeTickers, $base ){

        $MASSIV = [];

        foreach ($TickersBDIN as $ket=>$TickerWork)
        {

            if ($TickerWork['price'] == "none") continue;
            if ($TickerWork['ticker'] == $base) continue;
            if ($TickerWork['price'] == 0) continue;


            $TickerBirga = $TickerWork['ticker']."/".$base."";

            // ПРИ ДЕБАГЕ И РЕФАКТОРИНГЕ ТЕСТИТЬ И ПРОВЕРЯТЬ!!!!!
            if (empty(($ExchangeTickers[$TickerBirga]['close']))) continue;

            $ExPRICE = $ExchangeTickers[$TickerBirga]['close'];
            $change = changemet($TickerWork['price'], $ExPRICE );
 

            //  echo "Работаем с <b> ".$TickerWork['ticker']."</b> <br>";
            //  echo "За ".$startdeposit." ".$MethodENTER." покупаем ".$amountbuy." <b> ".$TickerWork['ticker']." </b> <br> ";
            //  echo "На бирже продаем ".$TickerWork['ticker']." за ".$MethodENTER." получаем  ".$amountsell." <br> ";
            // echo "Цена входа по обменникам: ".$TickerWork['price']."<br>";
            // echo "Цена продажи тикера: ".$ExPRICE."<br>";
            // echo "Спред захода: ".$change."<br>";

            $MASSIV['spred'][$TickerWork['ticker']] = $change;

            $MASSIV['enterprice'][$TickerWork['ticker']] = $TickerWork['price'];
            $MASSIV['exitprice'][$TickerWork['ticker']] = $ExPRICE;

            $MASSIV['url'][$TickerWork['ticker']] = $TickerWork['url'];
            $MASSIV['limit'][$TickerWork['ticker']] = $TickerWork['limit'];


        }

        arsort($MASSIV['spred']);


        return $MASSIV;


    }

    private function LoadObrabotka($ARR, $type, $exchange){
        $DATA = [];

        // echo "КАУНТ: ".count($ARR['spred'])."<br>";

        for ($i=0; $i<=count($ARR['spred']); $i++ ) {

            if (reset($ARR['spred']) < $this->minumumspred) continue;

            $MASS['exchange'] = $exchange;
            $MASS['type'] = $type;
            $MASS['symbol'] = array_key_first($ARR['spred']);
            $MASS['spred'] = reset($ARR['spred']);
            $MASS['enterprice'] = $ARR['enterprice'][$MASS['symbol']];
            $MASS['exitprice'] = $ARR['exitprice'][$MASS['symbol']];
            $MASS['url'] = $ARR['url'][$MASS['symbol']];
            $MASS['limit'] = $ARR['limit'][$MASS['symbol']];


            array_shift($ARR['spred']);

            $DATA[] = $MASS;

        }


        return $DATA;
    }







    private function LoadTickersBD($type, $method)
    {

        $table = [];
        if ($type == "IN") $table = R::findAll("obmenin", 'WHERE method=?', [$method]);
        if ($type == "OUT") $table = R::findAll("obmenout",'WHERE method=?', [$method]);

        return $table;
    }
    private function GetPriceAct($MONETA, $method){
        $zapis = R::findOne("obmenin", 'WHERE method =? AND ticker=?', [$method, $MONETA]);
        return $zapis['price'];

    }
    private function GetTickerText($exchange){

        $file = file_get_contents(WWW."/Ticker".$exchange.".txt");     // Открыть файл data.json
        $MASSIV = json_decode($file,TRUE);              // Декодировать в массив
        return $MASSIV;

    }






}
?>