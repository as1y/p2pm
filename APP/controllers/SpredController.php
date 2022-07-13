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

        $ENTER[] = "QIWI";
        $EXCHANGES[] = "Binance";
        $EXCHANGES[] = "Poloniex";
        $EXIT[] = "VISA";

        $STARTPRICE['BTC'] = $this->GetPriceAct("BTC");
        $STARTPRICE['ETH'] = $this->GetPriceAct("ETH");
        $STARTPRICE['USDT'] = $this->GetPriceAct("USDT");


        echo "<b>Стартовая цена захода BTC: </b>".$STARTPRICE['BTC']."<br>";
        echo "<b>Стартовая цена захода ETH: </b>".$STARTPRICE['ETH']."<br>";
        echo "<b>Стартовая цена захода USDT: </b>".$STARTPRICE['USDT']."<br>";


        // Рассчет самого выгодного входа через БИРЖУ


        $MassivPoloniexENTER =  $this->GetArrEnterExchange($STARTPRICE, "Poloniex", "QIWI", "VISA");
        show($MassivPoloniexENTER);


        $MassivBinanceENTER =  $this->GetArrEnterExchange($STARTPRICE, "Binance", "QIWI", "VISA");
        show($MassivBinanceENTER);

        $MassivGateENTER =  $this->GetArrEnterExchange($STARTPRICE, "Binance", "QIWI", "VISA");
        show($MassivGateENTER);




        echo "<hr>";



        // Данные на вход







//        $this->set(compact(''));

    }



    private function GetArrExitExchange(){


    }


    private function GetArrEnterExchange($STARTPRICE, $Exchange, $MethodENTER, $MethodEXIT){

        $FINALMASSIV = [];

        $TickersBDIN = $this->LoadTickersBD("IN", $MethodENTER);
        $ExchangeTickers = $this->GetTickerText($Exchange);

        $ArrBTC = $this->GetArrEnterBase($TickersBDIN, $ExchangeTickers, $STARTPRICE, "BTC");
        $ArrETH = $this->GetArrEnterBase($TickersBDIN, $ExchangeTickers, $STARTPRICE, "ETH");
        $ArrUSDT = $this->GetArrEnterBase($TickersBDIN, $ExchangeTickers, $STARTPRICE, "USDT");

        $FINALMASSIV = array_merge($ArrBTC, $ArrETH, $ArrUSDT);

      //  show($FINALMASSIV);

        $FINALMASSIV = $this->GetTopSpredsMassiv($FINALMASSIV, 5, $Exchange, $MethodENTER, $MethodEXIT);

        return $FINALMASSIV;

    }

    private function GetTopSpredsMassiv($FINALMASSIV, $count, $exname, $MethodENTER, $MethodEXIT){

        // Получаем 3 ТОП1 из всех
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

        $Dannie['symbol'] = $symbol;
        $Dannie['moneta'] = array_key_first($FINALMASSIV[$symbol]['spred']);
        $Dannie['spred'] = reset($FINALMASSIV[$symbol]['spred']);
        $Dannie['final'] = $FINALMASSIV[$symbol]['final'][$Dannie['moneta']];

        $Dannie['exitmoneta'] = $MASS['exitmoneta'];
        $Dannie['exitprice'] = $MASS['exitprice'];

        $Dannie['finalspred'] = changemet($Dannie['final'], $Dannie['exitprice']);


        return $Dannie;
    }

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

          //  echo "Обрабатываем тикер: ".$TickerWork['ticker']."<br>";
          //  echo "Финальная сумма захода: ".$FinalPrice."<br>";
          //  echo "Спред: ".$change."<br>";

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
    private function GetPriceAct($MONETA){
        $zapis = R::findOne("obmenin", 'WHERE method =? AND ticker=?', ["QIWI", $MONETA]);
        return $zapis['price'];

    }
    private function GetTickerText($exchange){

        $file = file_get_contents(WWW."/Ticker".$exchange.".txt");     // Открыть файл data.json
        $MASSIV = json_decode($file,TRUE);              // Декодировать в массив
        return $MASSIV;

    }






}
?>