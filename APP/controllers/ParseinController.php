<?php
namespace APP\controllers;
use APP\core\Cache;
use APP\models\Addp;
use APP\models\Panel;
use APP\core\base\Model;
use RedBeanPHP\R;

class ParseinController extends AppController {
    public $layaout = 'PANEL';
    public $BreadcrumbsControllerLabel = "Панель управления";
    public $BreadcrumbsControllerUrl = "/panel";

    public $ApiKey = "U5I2AoIrTk4gBR7XLB";
    public $SecretKey = "HUfZrWiVqUlLM65Ba8TXvQvC68kn1AabMDgE";

    public $TICKERSqiwiIN = [];
    public $TICKERSvisaOUT = [];

    public $vremya = 200; // Секунд



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
        // ПАРАМЕТРЫ ДЛЯ БАЗОВОЙ ТАБЛИЦЫ!

       $this->TICKERSqiwiIN = [

           "https://www.bestchange.ru/qiwi-to-bitcoin.html" => "BTC",
"https://www.bestchange.ru/qiwi-to-bitcoin-cash.html" => "BCH",
"https://www.bestchange.ru/qiwi-to-bitcoin-gold.html" => "BTG",
"https://www.bestchange.ru/qiwi-to-ethereum.html" => "ETH",
"https://www.bestchange.ru/qiwi-to-ethereum-classic.html" => "ETC",
"https://www.bestchange.ru/qiwi-to-litecoin.html" => "LTC",
"https://www.bestchange.ru/qiwi-to-ripple.html" => "XRP",
"https://www.bestchange.ru/qiwi-to-monero.html" => "XMR",
"https://www.bestchange.ru/qiwi-to-dogecoin.html" => "DOGE",
"https://www.bestchange.ru/qiwi-to-polygon.html" => "MATIC",
"https://www.bestchange.ru/qiwi-to-dash.html" => "DASH",
"https://www.bestchange.ru/qiwi-to-zcash.html" => "ZEC",
"https://www.bestchange.ru/qiwi-to-nem.html" => "XEM",
"https://www.bestchange.ru/qiwi-to-neo.html" => "NEO",
"https://www.bestchange.ru/qiwi-to-eos.html" => "EOS",
"https://www.bestchange.ru/qiwi-to-cardano.html" => "ADA",
"https://www.bestchange.ru/qiwi-to-stellar.html" => "XLM",
"https://www.bestchange.ru/qiwi-to-tron.html" => "TRX",
"https://www.bestchange.ru/qiwi-to-waves.html" => "WAVES",
"https://www.bestchange.ru/qiwi-to-omg.html" => "OMG",
"https://www.bestchange.ru/qiwi-to-binance-coin.html" => "BNB",
"https://www.bestchange.ru/qiwi-to-bat.html" => "BAT",
"https://www.bestchange.ru/qiwi-to-qtum.html" => "QTUM",
"https://www.bestchange.ru/qiwi-to-chainlink.html" => "LINK",
"https://www.bestchange.ru/qiwi-to-cosmos.html" => "ATOM",
"https://www.bestchange.ru/qiwi-to-tezos.html" => "XTZ",
"https://www.bestchange.ru/qiwi-to-polkadot.html" => "DOT",
"https://www.bestchange.ru/qiwi-to-uniswap.html" => "UNI",
"https://www.bestchange.ru/qiwi-to-ravencoin.html" => "RVN",
"https://www.bestchange.ru/qiwi-to-solana.html" => "SOL",
"https://www.bestchange.ru/qiwi-to-vechain.html" => "VET",
"https://www.bestchange.ru/qiwi-to-algorand.html" => "ALGO",
"https://www.bestchange.ru/qiwi-to-maker.html" => "MKR",
"https://www.bestchange.ru/qiwi-to-avalanche.html" => "AVAX",
"https://www.bestchange.ru/qiwi-to-yearn-finance.html" => "YFI",
"https://www.bestchange.ru/qiwi-to-terra.html" => "LUNA",
"https://www.bestchange.ru/qiwi-to-tether-erc20.html" => "USDT",
"https://www.bestchange.ru/qiwi-to-bitcoin-sv.html" => "BSV",
"https://www.bestchange.ru/qiwi-to-zrx.html" => "ZRX",
"https://www.bestchange.ru/qiwi-to-icon.html" => "ICX",
"https://www.bestchange.ru/qiwi-to-ontology.html" => "ONT",
"https://www.bestchange.ru/qiwi-to-ton.html" => "TON"

        ];



       echo "<h2>PARSE-IN-V2 | ПАРСИНГ BESTCHANGE</h2><br>";



        // БАЗОВАЯ ТАБЛИЦА С ТИКЕРАМИ
       $basetable =  $this->GetBaseTable("IN"); // Создаем BaseTickers
        if (empty($basetable))
        {
            $this->WorkTable($basetable); // Если таблица пустая, то создаем
            exit("::");
        }


        // БАЗОВАЯ ТАБЛИЦА С ТИКЕРАМИ

        // Инициализация парсера
        $aparser = new \Aparser('http://91.210.171.153:9091/API', '', array('debug'=>true));


        // ОБНОВЛЕНИЕ ПАРСИНГА IN!!!!!

        $StatusTable =  $this->GetStatusTable("IN"); // Таблица статусТейбл

        // Если таблица статуса парсинга пустая, то запускаем парсинг
        if (empty($StatusTable))
        {

            // Проверяем созданную таблицу страниц
            foreach ($basetable as $key=>$val)
            {

                $proshlo = time() - $val['time'];

                echo "URL: ".$val['price']."<br>";
                echo "Цена в БД: ".$val['price']."<br>";
                echo "Прошло времени: ".$proshlo."<br>";

                if ($proshlo > $this->vremya)
                {
                    echo "<i>Цена не актуальная! Пора обновлять</i><br>";
                    $ZaprosiIN[] = $val['url'];
                }

                echo "<hr>";




            }


            if (empty($ZaprosiIN))
            {
                echo "<font color='green'>Информация актуальная. Парсить нет необходимости </font><br>";
                return true;
            }


            echo "Кол-во запросов ".(count($ZaprosiIN))."<br>";


            $taskUid = $aparser->addTask('20', 'BestIN', 'text', $ZaprosiIN);
            $this->AddTaskBD($taskUid, "IN");
            return true;

        }

        // Смотрим СТАТУС!
        $AparserIN =   $aparser->getTaskState($StatusTable['taskid']);

      if ($AparserIN['status'] == "work")
      {
          echo "ПАУЗА<br>";
          echo "<font color='#8b0000'>ПАРСИНГ IN В РАБОТЕ</font><br>";
          sleep(10);

      }



        if ($AparserIN['status'] == "completed"){

            echo "<font color='green'>ПАРСИНГ IN ЗАКОНЧЕН</font><br>";

            $result = $aparser->getTaskResultsFile($StatusTable['taskid']);
            $content = file_get_contents($result);
            $content = str_replace(" ", "", $content); // Убираем пробелы
            $content = explode("\n", $content);

            // ОБНОВЛЯЕМ ТАБЛИЦУ
           // show($content);

            // Обновляем в БД цены
            $this->RenewTickers($content, "IN");

            // Очищаем статус таблицу
            R::trash($StatusTable);


        }






//        $this->set(compact(''));

    }



    private function WorkTable($basetable)
    {

        if (empty($basetable))
        {
            // СОЗДАЕМ ТАБЛИЦУ НА КИВИ ВХОД
            foreach ($this->TICKERSqiwiIN as $url => $ticker)
            {
                $ZAPIS['global'] = "QIWI";
                $ZAPIS['type'] = "IN";
                $ZAPIS['url'] = $url;
                $ZAPIS['ticker'] = $ticker;
                $this->AddTable($ZAPIS);
            }
            echo "<hr>";



            echo "<font color='green'>Таблица с тикерами создана!</font> <br>";
        }


        return true;

    }


    private function RenewTickers($content, $type)
    {


        echo "МАССИВ ПАРСИНГА<br>";


        // Преобразовываем массив в примемлемый вид
        $MASSIV = [];
        foreach ($content as $key=>$value)
        {
            if (empty($value)) continue;
            $value = explode(";", $value);
 //           show($value);
            $MASSIV[$value[0]][] = $value[1];
            $MASSIV[$value[0]][] = $value[2];
        }



        $TICKERS = $this->GetBaseTable($type);


      //  show($MASSIV);


        // Добавляем в БД данные из спарсенного контента!
        foreach ($TICKERS as $ticker)
        {

            if (empty($MASSIV[$ticker['url']])) continue;
            if ($MASSIV[$ticker['url']][0] == "none") continue;

           $ticker->price = $MASSIV[$ticker['url']][0];
           $ticker->limit = $MASSIV[$ticker['url']][1];
           $ticker->time = time();
            R::store($ticker);

        }


        return true;


    }



    private function AddTaskBD($taskid, $type)
    {

        $ARR = [];

        if ($type == "IN")
        {
            $ARR['taskid'] = $taskid;
            $ARR['type'] = "IN";
        }
        if ($type == "OUT"){
            $ARR['taskid'] = $taskid;
            $ARR['type'] = "OUT";

        }
        $this->AddARRinBD($ARR, "statustable");
        echo "<b><font color='green'>Добавили запись</font></b>";
        // Добавление ТРЕКА в БД

    }




    private function AddTable($ZAPIS)
    {

        $ARR['global'] = $ZAPIS['global'];
        $ARR['type'] = $ZAPIS['type'];
        $ARR['url'] = $ZAPIS['url'];
        $ARR['ticker'] = $ZAPIS['ticker'];


        $this->AddARRinBD($ARR, "basetickers");
        echo "<b><font color='green'>Добавили запись</font></b>";
        // Добавление ТРЕКА в БД


        return true;

    }


    private function GetBaseTable($type)
    {
        $table = R::findAll("basetickers", "WHERE type=?", [$type]);
        return $table;
    }


    private function GetStatusTable($type)
    {
        $table = R::findOne("statustable", "WHERE type=?", [$type]);
        return $table;
    }






    private function AddARRinBD($ARR, $BD = false)
    {

        $tbl = R::dispense($BD);
        //ДОБАВЛЯЕМ В ТАБЛИЦУ

        foreach ($ARR as $name => $value) {
            $tbl->$name = $value;
        }

        $id = R::store($tbl);

        echo "<font color='green'><b>ДОБАВИЛИ ЗАПИСЬ В БД!</b></font><br>";

        return $id;


    }




}
?>