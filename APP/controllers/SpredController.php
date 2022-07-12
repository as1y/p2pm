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
        $MassivBinance =  $this->GetArrEnterExchange($STARTPRICE, "Binance", "QIWI");
        show($MassivBinance);


       // $MassivPoloniex =  $this->GetArrEnterExchange($STARTPRICE, "Poloniex", "QIWI");

  //      show($MassivBinance);

        echo "<hr>";

  //      show($MassivPoloniex);



        // Отранжированный массив

        // Монета покупаемая в обменнике
        // Монета получаемая на бирже
        // Цена покупки
        // Выгода в процентах от основной цены


        // Данные на вход





        exit("11");



        $TickersBDIN = $this->LoadTickersBD("IN", "QIWI");

        $TickersBDOUT = $this->LoadTickersBD("OUT");


        // ЗАГРУЗКА ТИКЕРОВ БИРЖ


        echo "<hr>";

        echo "<h2>BINANCE</h2>";
        $this->CalculateExit("USDT", $TickersBDOUT, $ALLBinance);

        echo "<h2>POLONIEX</h2>";
        $this->CalculateExit("USDT",$TickersBDOUT, $AllPolonex);

        echo "<hr>";


        echo "<h3>ВХОД ЧЕРЕЗ МОНЕТУ - ETH (BINANCE)</h3>";
        $RENDER = $this->CheckBestPrice("BTC", "Binance",$TickersBDIN, $STARTPRICE, $ALLBinance);
        echo "<b>Самый выгодный символ:</b> ".$RENDER['BestSpredSymbol']." <br>";

        echo "Лучшая цена ".$RENDER['BestPrice']."<br>";

        echo "<h2>BINANCE</h2>";
        $this->CalculateExit("BTC", $TickersBDOUT, $ALLBinance);

        echo "<h2>POLONIEX</h2>";
        $this->CalculateExit("BTC",$TickersBDOUT, $AllPolonex);

        echo "<hr>";



       /*

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

    */



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



    private function GetArrEnterExchange($STARTPRICE, $Exchange, $Method){

        $FINALMASSIV = [];

        $TickersBDIN = $this->LoadTickersBD("IN", $Method);
        $ExchangeTickers = $this->GetTickerText($Exchange);

        $ArrBTC = $this->GetArrEnterBase($TickersBDIN, $ExchangeTickers, $STARTPRICE, "BTC");
        $ArrETH = $this->GetArrEnterBase($TickersBDIN, $ExchangeTickers, $STARTPRICE, "ETH");
        $ArrUSDT = $this->GetArrEnterBase($TickersBDIN, $ExchangeTickers, $STARTPRICE, "USDT");

        $FINALMASSIV = array_merge($ArrBTC, $ArrETH, $ArrUSDT);

        show($FINALMASSIV);

        $FINALMASSIV = $this->GetTopSpredsMassiv($FINALMASSIV, 5);

        return $FINALMASSIV;

    }



    private function GetTopSpredsMassiv($FINALMASSIV, $count){

        // Получаем 3 ТОП1 из всех
        $OBRABOTKA = [];

      //  show($FINALMASSIV);

        // Цикл на ОТБОР 5 ЛУЧШИХ
            for ($i=0; $i<$count; $i++ ){

                $firstBTC = reset($FINALMASSIV['BTC']['spred']);
                $firstETH = reset($FINALMASSIV['ETH']['spred']);
                $firstUSDT = reset($FINALMASSIV['USDT']['spred']);

                /*
                echo "Первый элемент БТЦ ".$firstBTC."<br>";
                echo "Первый элемент ETH ".$firstETH."<br>";
                echo "Первый элемент USDt ".$firstUSDT."<br>";
                */

                if ($firstBTC > $firstETH && $firstBTC > $firstUSDT)
                {
                    $OBRABOTKA[] = $this->LoadObrabotka("BTC", $FINALMASSIV);
                    array_shift($FINALMASSIV['BTC']['spred']);

                }
                if ($firstETH > $firstBTC && $firstETH > $firstUSDT)
                {
                    $OBRABOTKA[] = $this->LoadObrabotka("ETH", $FINALMASSIV);
                    array_shift($FINALMASSIV['ETH']['spred']);
                }
                if ($firstUSDT > $firstETH && $firstUSDT > $firstBTC)
                {
                    $OBRABOTKA[] = $this->LoadObrabotka("USDT", $FINALMASSIV);
                    array_shift($FINALMASSIV['USDT']['spred']);
                }

            }



        return $OBRABOTKA;

    }


    private function LoadObrabotka($symbol, $FINALMASSIV){

        $Dannie['symbol'] = $symbol;
        $Dannie['moneta'] = array_key_first($FINALMASSIV[$symbol]['spred']);
        $Dannie['spred'] = reset($FINALMASSIV[$symbol]['spred']);
        $Dannie['final'] = $FINALMASSIV[$symbol]['final'][$Dannie['moneta']];


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

/*
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


            $RENDER =  $this->RenderPercent($RENDER, $TickerWork, $ExchangeTicker, $ExPRICE, $MONETA, $STARTPRICE, $ExchangeName);



        }


        echo "<hr>";

        return $RENDER;


    }
*/






    private function CalculateExit($base, $TickersBDOUT, $AllExchange){

        // Поиск лучшего выхода по всей биржи

        //    $base = "USDT";
        //    $baseprice = "64.16";

        $BestPrice = 0;
        $BestTicker = "";


        foreach ($TickersBDOUT as $key=>$val)
        {


            $exticker = $val['ticker']."/".$base;


            if ($val['ticker'] == $base) continue;
            if (empty($AllExchange[$exticker]['close'])) continue;

            $amount = 1/$AllExchange[$exticker]['close'];
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
                $BestTicker = $exticker;

            }

            //     echo "<hr>";



        }


        echo "<b>Монета выхода: </b>".$BestTicker."<br>";
        echo "<b>Лучшая цена выхода: </b>".$BestPrice."<br>";




        return true;
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