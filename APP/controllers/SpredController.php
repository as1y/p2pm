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
        $this->EXCHANGES[] = "Ftx";
        $this->EXCHANGES[] = "Exmo";
        $this->EXCHANGES[] = "Kucoin";
        $this->EXCHANGES[] = "Okex";


        $ENTER[] = "QIWI";


        $EXIT[] = "VISA";



        // Рассчет самого выгодного входа через БИРЖУ

        echo "<h2><font color='#8b0000'>СВЯЗКИ VISA-EXCHANGE-VISA</font></h2>";
        foreach ($this->EXCHANGES as $key=>$exchange){

            $STARTPRICE['BTC'] = $this->GetPriceAct("BTC", "VISA");
            $STARTPRICE['ETH'] = $this->GetPriceAct("ETH", "VISA");
            $STARTPRICE['USDT'] = $this->GetPriceAct("USDT", "VISA");

            $MassivEnter =  $this->GetArrEnterExchange($STARTPRICE, $exchange, "VISA", "VISA");
           // show($MassivEnter);
            $this->RenderFinalExchange($MassivEnter, $exchange, "VISA");

        }


        echo "<h2><font color='#8b0000'>СВЯЗКИ USDT-EXCHANGE-USDT</font></h2>";
        foreach ($this->EXCHANGES as $key=>$exchange){

            $STARTPRICE['BTC'] = $this->GetPriceAct("BTC", "USDT");
            $STARTPRICE['ETH'] = $this->GetPriceAct("ETH", "USDT");
            $STARTPRICE['USDT'] = $this->GetPriceAct("USDT", "USDT");


            $MassivEnter =  $this->GetArrEnterExchange($STARTPRICE, $exchange, "USDT", "USDT");
          //  show($MassivEnter);
            $this->RenderFinalExchange($MassivEnter, $exchange, "USDT");

        }






        echo "<hr>";



        // Данные на вход







//        $this->set(compact(''));

    }





    private function RenderFinalExchange($MassivEX, $exname, $Method){




       // show($MassivEX);

//        if (empty($MassivEX))
//        {
//            echo "Связок через биржу <b>".$exname."</b> и точку входа ".$Method." на данный момент нет :( ";
//            return true;
//        }

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


/*
    private function RenderFinalExchangePerebros($MassivEX, $MethodEXIT){


        show($MassivEX);

        $TickersBDOUT = $this->LoadTickersBD("OUT", $MethodEXIT);
        $ExchangeTickers = $this->GetTickerText("Poloniex");


        foreach ($MassivEX as $key=>$value)
        {

            $STARTPRICE = $value['final'];
            $SYMBOL = $value['symbol'];
            $this->GetArrExitBase($TickersBDOUT, $ExchangeTickers, $STARTPRICE, $SYMBOL);
            echo "<hr>";

        }


        // Забор лучшего выхода с биржи (со спредами)





        return true;
    }
*/


    private function GetArrEnterExchange($STARTPRICE, $Exchange, $MethodENTER, $MethodEXIT){


        $TickersBDIN = $this->LoadTickersBD("IN", $MethodENTER);
        $ExchangeTickers = $this->GetTickerText($Exchange);


        $ArrBTC = $this->GetArrEnterBase($TickersBDIN, $ExchangeTickers, $STARTPRICE, "BTC");
        $ArrETH = $this->GetArrEnterBase($TickersBDIN, $ExchangeTickers, $STARTPRICE, "ETH");
        $ArrUSDT = $this->GetArrEnterBase($TickersBDIN, $ExchangeTickers, $STARTPRICE, "USDT");

        $FINALMASSIV = array_merge($ArrBTC, $ArrETH, $ArrUSDT);

      //  show($FINALMASSIV);

        $OBRABOTKA = $this->GetTopSpredsMassiv($FINALMASSIV, 5, $Exchange, $MethodENTER, $MethodEXIT);

        return $OBRABOTKA;

    }

    private function GetTopSpredsMassiv($FINALMASSIV, $count, $exname, $MethodENTER, $MethodEXIT){

        $OBRABOTKA = [];

       // show($FINALMASSIV);

        // Цикл на ОТБОР 5 ЛУЧШИХ
            for ($i=0; $i<$count; $i++ ){

                $firstBTC = reset($FINALMASSIV['BTC']['spred']);
                $firstETH = reset($FINALMASSIV['ETH']['spred']);
                $firstUSDT = reset($FINALMASSIV['USDT']['spred']);


             //   echo "Первый элемент БТЦ ".$firstBTC."<br>";
            //    echo "Первый элемент ETH ".$firstETH."<br>";
            //    echo "Первый элемент USDt ".$firstUSDT."<br>";


                if ($firstBTC > $firstETH && $firstBTC > $firstUSDT)
                {
                    $OBRABOTKA[] = $this->LoadObrabotka("BTC", $FINALMASSIV, $exname, $MethodEXIT);
                    array_shift($FINALMASSIV['BTC']['spred']);

                }
                if ($firstETH > $firstBTC && $firstETH > $firstUSDT)
                {
                    $OBRABOTKA[] = $this->LoadObrabotka("ETH", $FINALMASSIV, $exname, $MethodEXIT);
                    array_shift($FINALMASSIV['ETH']['spred']);
                }
                if ($firstUSDT > $firstETH && $firstUSDT > $firstBTC)
                {
                    $OBRABOTKA[] = $this->LoadObrabotka("USDT", $FINALMASSIV, $exname, $MethodEXIT);
                    array_shift($FINALMASSIV['USDT']['spred']);
                }

            }



        return $OBRABOTKA;

    }


    private function LoadObrabotka($symbol, $FINALMASSIV, $exname, $methodname){

        $MASS = $this->CalculateExit($symbol, $exname, $methodname);

        $Dannie['moneta'] = array_key_first($FINALMASSIV[$symbol]['spred']);

        $Dannie['exname'] = $exname;
        $Dannie['symbol'] = $symbol;

        $Dannie['spred'] = reset($FINALMASSIV[$symbol]['spred']);
        $Dannie['final'] = $FINALMASSIV[$symbol]['final'][$Dannie['moneta']];

        $Dannie['exitmoneta'] = $MASS['exitmoneta'];
        $Dannie['exitprice'] = $MASS['exitprice'];

        $Dannie['finalspred'] = changemet($Dannie['final'], $Dannie['exitprice']);


        return $Dannie;
    }


    /*
    private function GetArrExitBase($TickersBDOUT, $ExchangeTickers, $STARTPRICE, $MONETA){

        $MASSIV[$MONETA] = [];

        echo "<b>Работаем с монетой</b> ".$MONETA." <br>";

        echo "<b>Цена покупки</b> ".$STARTPRICE." <br>";


        foreach ($TickersBDOUT as $TickerWork){

            if ($TickerWork['price'] == "none") continue;
            if ($TickerWork['ticker'] == $MONETA) continue;

            $TickerBirga = $TickerWork['ticker']."/".$MONETA."";

           // if ($MONETA == "BTC" && $TickerBirga == "USDT/BTC") $TickerBirga = "BTC/USDT";
          //  if ($MONETA == "ETH" && $TickerBirga == "BTC/ETH") $TickerBirga = "ETH/BTC";
          //  if ($MONETA == "ETH" && $TickerBirga == "USDT/ETH") $TickerBirga = "ETH/USDT";

            if (empty(($ExchangeTickers[$TickerBirga]['close']))) continue;

            $ExPRICE = $ExchangeTickers[$TickerBirga]['close'];

            if (empty($ExPRICE)) continue;

            $FinalPrice = $TickerWork['price']/$ExPRICE;
            $FinalPrice = round($FinalPrice, 2);
            $change = changemet($STARTPRICE, $FinalPrice );



            echo "Обрабатываем тикер: ".$TickerWork['ticker']."<br>";
            echo "Тикер на бирже: ".$TickerBirga."<br>";
            echo "Цена на бирже: ".$ExPRICE."<br>";

            echo "Цена продажи: ".$FinalPrice."<br>";
            echo "Спред: ".$change."<br>";




        }

        return $MASSIV;
    }
*/
    private function GetArrEnterBase($TickersBDIN, $ExchangeTickers, $STARTPRICE, $MONETA){

        $MASSIV[$MONETA] = [];


        foreach ($TickersBDIN as $TickerWork)
        {
            if ($TickerWork['price'] == "none") continue;
            if ($TickerWork['ticker'] == $MONETA) continue;

            //
            $TickerBirga = $TickerWork['ticker']."/".$MONETA."";
            if ($MONETA == "BTC" && $TickerBirga == "USDT/BTC") $TickerBirga = "BTC/USDT";
            if ($MONETA == "ETH" && $TickerBirga == "BTC/ETH") $TickerBirga = "ETH/BTC";
            if ($MONETA == "ETH" && $TickerBirga == "USDT/ETH") $TickerBirga = "ETH/USDT";

            if (empty(($ExchangeTickers[$TickerBirga]['close']))) continue;

            $ExPRICE = $ExchangeTickers[$TickerBirga]['close'];

            // Перекрестные тикеры
            if ($MONETA == "BTC" && $TickerBirga == "BTC/USDT") $ExPRICE = 1 / $ExPRICE;
            if ($MONETA == "ETH" && $TickerBirga == "ETH/BTC") $ExPRICE = 1 / $ExPRICE;
            if ($MONETA == "ETH" && $TickerBirga == "ETH/USDT") $ExPRICE = 1 / $ExPRICE;

            if (empty($ExPRICE)) continue;

            $FinalPrice = $TickerWork['price']/$ExPRICE;
            $FinalPrice = round($FinalPrice, 2);
            $change = changemet($FinalPrice, $STARTPRICE[$MONETA] );


         //   echo "Обрабатываем тикер: ".$TickerWork['ticker']."<br>";
         //   echo "Финальная сумма захода: ".$FinalPrice."<br>";
       //     echo "Базовая сумма захода".$STARTPRICE[$MONETA]."";
         //   echo "Спред: ".$change."<br>";

            // СПРЕД
            $MASSIV[$MONETA]['spred'][$TickerWork['ticker']] = $change;
            $MASSIV[$MONETA]['final'][$TickerWork['ticker']] = $FinalPrice;

            arsort($MASSIV[$MONETA]['spred']);


          //  $MASSIV[$MONETA][$TickerWork['ticker']]['finalprice'] = $FinalPrice;
            // $RENDER =  $this->RenderPercent($RENDER, $TickerWork, $TickerBirga, $ExPRICE, $MONETA, $STARTPRICE);

        }

        return $MASSIV;

    }




    private function CalculateExit($base, $Exchange, $method){

        $ExchangeTickers = $this->GetTickerText($Exchange);
        $TickersBDOUT = $this->LoadTickersBD("OUT", $method);


        $BestPrice = 0;
        $BestTicker = "";



        foreach ($TickersBDOUT as $key=>$val)
        {


            $exticker = $val['ticker']."/".$base;


            if ($val['ticker'] == $base) continue;
            if (empty($ExchangeTickers[$exticker]['close'])) continue;

            $amount = 1/$ExchangeTickers[$exticker]['close'];
            $amount = round($amount, 10);


            $final = $amount*$val['price'];

            //         echo "Монета: ".$val['ticker']."<br>";
            //          echo "Цена: ".$val['price']."<br>";

            //            echo "Цена актива на бирже: ".$AllExchange[$exticker]['close']."<br>";
            //             echo "Какое кол-во актива получим с обмена: ".$amount."<br>";
            //              echo "Сколько получим после продажи актива в обменниках: ".$final."<br>";

            if ($final > $BestPrice)
            {
                $BestPrice = $final;
                $BestTicker = $val['ticker'];

            }



        }

        $MASS['exitmoneta'] = $BestTicker;
        $MASS['exitprice'] = $BestPrice;



        return $MASS;
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