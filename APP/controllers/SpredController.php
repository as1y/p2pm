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


    public $minumumspred = 0.3;


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
 //       $this->EXCHANGES[] = "Gateio";
 //       $this->EXCHANGES[] = "Huobi";
   //     $this->EXCHANGES[] = "Ftx";
   //     $this->EXCHANGES[] = "Exmo";
  //      $this->EXCHANGES[] = "Kucoin";
  //      $this->EXCHANGES[] = "Okex";



      //  echo "<h2><font color='#8b0000'>СВЯЗКИ USDT-EXCHANGE-USDT</font></h2>";
        $Base = "USDT";

        foreach ($this->EXCHANGES as $key=>$exchange)
        {

        //    echo "<h2>СКАН ".$exchange." </h2>";
            $MassivWork =  $this->GetArrWorkExchange($exchange, $Base);

            //show($MassivWork);

            $this->RenderFinalExchange($MassivWork, $exchange, "USDT");







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

      //  show($Obrabotka);


        $ArrExit =  $this->GetArrExit($TickersBDOUT, $ExchangeTickers, $base);
        $Obrabotka['exit'] = $this->LoadObrabotka($ArrExit, "exit", $exchange);





        return $Obrabotka;

    }


    private function RenderFinalExchange($MassivEX, $exname, $Method){


        echo "<h3>Вход - ".$exname."</h3>";

        foreach ($MassivEX['enter'] as $key=>$val){
            echo "<b>1.</b> Через <a href='".$val['url']."' target='_blank'>BestChange</a> меняем <b>".$Method."</b> на <b>".$val['symbol']."</b>. Цена ~ ".$val['enterprice']."  Вводим кошелек для зачисления биржи <b>".$exname."</b>";
            echo " <a href='".$val['redirect']."' target='_blank'><b>ССЫЛКА НА ОБМЕННИК</b></a>  <br>";
            echo "<b>2.</b> На бирже <b>".$exname."</b> монету <b>".$val['symbol']."</b>  меняем на <b>".$Method."</b>  Цена ~ ".$val['exitprice']." <br>";
            echo "<b>3.</b> Зарабатываем <b> <font color='green'>".$val['spred']."% </font></b> с круга <br>";
            echo "<b>4.</b> МИН: <b> <font color='#b8860b'>".$val['limit']."</font></b> ".$Method." <br>";
            echo "<hr>";

        }

        echo "<h3>Выход - ".$exname."</h3>";

        foreach ($MassivEX['exit'] as $key=>$val){

            echo "<b>1.</b> На бирже <b>".$exname."</b> покупаем монету <b>".$val['symbol']."</b>  за  <b>".$Method."</b>  <b>Рекомендумая цена</b>  ~  ".$val['enterprice']." </b> <br>";
            echo "<b>2.</b> Через <a href='".$val['url']."' target='_blank' >BestChange</a> меняем <b>".$val['symbol']."</b> на <b>".$Method."</b>. <b>Рекомендумая цена</b>  ~  ".$val['exitprice']." </b> Вводим кошелек для зачисления биржи <b>".$exname."</b>";
            echo " <a href='".$val['redirect']."' target='_blank'><b>ССЫЛКА НА ОБМЕННИК</b></a>  <br>";
            echo "<b>3.</b> Зарабатываем <b> <font color='green'>".$val['spred']."% </font></b> с круга <br>";
            echo "<b>4.</b> МИН: <b> <font color='#b8860b'>".$val['limit']."</font></b> ".$Method." <br>";

            echo "<hr>";

        }






        return true;
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

            $ExPRICE = ($ExchangeTickers[$TickerBirga]['bid']+$ExchangeTickers[$TickerBirga]['ask'])/2;

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

            $MASSIV['redirect'][$TickerWork['ticker']] = $TickerWork['redirect'];

            $MASSIV['limit'][$TickerWork['ticker']] = $TickerWork['limit'];


        }

        arsort($MASSIV['spred']);


        return $MASSIV;


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

      //      $ExPRICE = ($ExchangeTickers[$TickerBirga]['bid']+$ExchangeTickers[$TickerBirga]['ask'])/2;

            $ExPRICE = $ExchangeTickers[$TickerBirga]['bid'];


            $change = changemet($ExPRICE, $TickerWork['price'] );




            //   echo "Работаем с <b> ".$TickerWork['ticker']."</b> <br>";
            //    echo "На бирже покупаем ".$TickerWork['ticker']." за ".$base." получаем  ".$TickerWork['ticker']." <br> ";
            //    echo "Цена покупки на бирже: ".$ExPRICE."<br>";
            //    echo "Цена продажи по обменникам: ".$TickerWork['price']."<br>";
            //    echo "Спред выхода: ".$change."<br>";


            $limit =  round($TickerWork['limit']*$TickerWork['price']);

            $MASSIV['spred'][$TickerWork['ticker']] = $change;

            $MASSIV['enterprice'][$TickerWork['ticker']] = $ExPRICE;
            $MASSIV['exitprice'][$TickerWork['ticker']] = $TickerWork['price'];

            $MASSIV['redirect'][$TickerWork['ticker']] = $TickerWork['redirect'];

            $MASSIV['url'][$TickerWork['ticker']] = $TickerWork['url'];
            $MASSIV['limit'][$TickerWork['ticker']] = $limit;




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
            $MASS['redirect'] = $ARR['redirect'][$MASS['symbol']];

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