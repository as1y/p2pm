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

    public $TICKERSqiwiIN = [];


    public $vremya = 150; // Секунд
    public $type = "IN";
    public $debug = false;
    public $sleep = 10;


    // ТЕХНИЧЕСКИЕ ПЕРЕМЕННЫЕ
    public function indexAction()
    {

        $this->layaout = false;
        $Panel =  new Panel();


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

        ];


       echo "<h2>PARSE-IN-V2 | ПАРСИНГ BESTCHANGE</h2><br>";

        // Инициализация парсера
        $aparser = new \Aparser('http://91.210.171.153:9091/API', '', array('debug'=>$this->debug));

        // Таблица статуса работы парсера
        $StatusTable =  $this->GetStatusTable();

        // Парсер не запущен. Формируем запрос;
        if (empty($StatusTable))
        {

            $ZAPROS = $this->GetZapros();

            if (empty($ZAPROS))
            {
                echo "<font color='green'>Информация актуальная. Парсить нет необходимости </font><br>";
                return true;
            }

            $taskUid = $aparser->addTask('20', 'BestIN', 'text', $ZAPROS);
            $this->AddTaskBD($taskUid, $this->type);
            return true;

        }


        // Смотрим СТАТУС!
        $AparserIN =   $aparser->getTaskState($StatusTable['taskid']);


      if ($AparserIN['status'] == "work")
      {
          echo "<font color='#8b0000'>ПАРСИНГ IN В РАБОТЕ</font><br>";
          sleep(rand($this->sleep, $this->sleep*2));
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
            $this->RenewTickers($content, $this->type);

            // Очищаем статус таблицу
            R::trash($StatusTable);


        }






//        $this->set(compact(''));

    }






    private function GetZapros(){

        $ZAPROS = [];

        $obmen =  $this->GetBaseTable(); // Создаем BaseTickers
        if (empty($obmen)) $this->CreateTable(); // Если таблица пустая, то создаем


        // Проверяем созданную таблицу страниц
        foreach ($obmen as $key=>$val)
        {

            $proshlo = time() - $val['time'];

            echo "Method: ".$val['method']."<br>";
            echo "Ticker: ".$val['ticker']."<br>";
            echo "Прошло: ".$proshlo."<br>";

            if ($proshlo > $this->vremya)
            {
                echo "<i>Цена не актуальная! Пора обновлять</i><br>";
                $ZAPROS[] = $val['url'];
            }

        }

        return $ZAPROS;

    }

    private function CreateTable()
    {


        // СОЗДАЕМ ТАБЛИЦУ НА КИВИ ВХОД
        foreach ($this->TICKERSqiwiIN as $url => $ticker)
        {
            $ARR['method'] = "QIWI";
            $ARR['url'] = $url;
            $ARR['ticker'] = $ticker;
            $this->AddARRinBD($ARR, "obmenin");
            //echo "<b><font color='green'>Добавили запись</font></b>";
            // Добавление ТРЕКА в БД
        }

        echo "<hr>";
        echo "<font color='green'>Таблица с ценами из обменников создана!!</font> <br>";

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



        $obmen = $this->GetBaseTable();


      //  show($MASSIV);


        // Добавляем в БД данные из спарсенного контента!
        foreach ($obmen as $ticker)
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
        $ARR['taskid'] = $taskid;
        $ARR['type'] = $type;

        $this->AddARRinBD($ARR, "statustable");
        echo "<b><font color='green'>Добавили запись</font></b>";
        // Добавление ТРЕКА в БД

    }

    private function GetBaseTable()
    {
        $table = R::findAll("obmenin");
        return $table;
    }

    private function GetStatusTable()
    {

        $table = R::findOne("statustable", "WHERE type=?", [$this->type]);
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