<?php
namespace APP\controllers;
use APP\core\Cache;
use APP\models\Addp;
use APP\models\Panel;
use APP\core\base\Model;
use RedBeanPHP\R;

class ParseoutController extends AppController {
    public $layaout = 'PANEL';
    public $BreadcrumbsControllerLabel = "Панель управления";
    public $BreadcrumbsControllerUrl = "/panel";

    public $TICKERSvisaOUT = [];


    public $vremya = 150; // Секунд
    public $type = "OUT";
    public $debug = false;
    public $sleep = 10;


    // ТЕХНИЧЕСКИЕ ПЕРЕМЕННЫЕ
    public function indexAction()
    {

        $this->layaout = false;
        $Panel =  new Panel();


        $this->TICKERSvisaOUT = [
            'https://www.bestchange.ru/bitcoin-to-visa-mastercard-rub.html' => 'BTC',
            'https://www.bestchange.ru/bitcoin-cash-to-visa-mastercard-rub.html' => 'BCH',
            'https://www.bestchange.ru/bitcoin-gold-to-visa-mastercard-rub.html' => 'BTG',
            'https://www.bestchange.ru/ethereum-to-visa-mastercard-rub.html' => 'ETH',
            'https://www.bestchange.ru/ethereum-classic-to-visa-mastercard-rub.html' => 'ETC',
            'https://www.bestchange.ru/litecoin-to-visa-mastercard-rub.html' => 'LTC',
            'https://www.bestchange.ru/ripple-to-visa-mastercard-rub.html' => 'XRP',
            'https://www.bestchange.ru/monero-to-visa-mastercard-rub.html' => 'XMR',
            'https://www.bestchange.ru/dogecoin-to-visa-mastercard-rub.html' => 'DOGE',
            'https://www.bestchange.ru/polygon-to-visa-mastercard-rub.html' => 'MATIC',
            'https://www.bestchange.ru/dash-to-visa-mastercard-rub.html' => 'DASH',
            'https://www.bestchange.ru/zcash-to-visa-mastercard-rub.html' => 'ZEC',
            'https://www.bestchange.ru/nem-to-visa-mastercard-rub.html' => 'XEM',
            'https://www.bestchange.ru/neo-to-visa-mastercard-rub.html' => 'NEO',
            'https://www.bestchange.ru/eos-to-visa-mastercard-rub.html' => 'EOS',
            'https://www.bestchange.ru/cardano-to-visa-mastercard-rub.html' => 'ADA',
            'https://www.bestchange.ru/stellar-to-visa-mastercard-rub.html' => 'XLM',
            'https://www.bestchange.ru/tron-to-visa-mastercard-rub.html' => 'TRX',
            'https://www.bestchange.ru/waves-to-visa-mastercard-rub.html' => 'WAVES',
            'https://www.bestchange.ru/omg-to-visa-mastercard-rub.html' => 'OMG',
            'https://www.bestchange.ru/binance-coin-to-visa-mastercard-rub.html' => 'BNB',
            'https://www.bestchange.ru/bat-to-visa-mastercard-rub.html' => 'BAT',
            'https://www.bestchange.ru/qtum-to-visa-mastercard-rub.html' => 'QTUM',
            'https://www.bestchange.ru/chainlink-to-visa-mastercard-rub.html' => 'LINK',
            'https://www.bestchange.ru/cosmos-to-visa-mastercard-rub.html' => 'ATOM',
            'https://www.bestchange.ru/tezos-to-visa-mastercard-rub.html' => 'XTZ',
            'https://www.bestchange.ru/polkadot-to-visa-mastercard-rub.html' => 'DOT',
            'https://www.bestchange.ru/uniswap-to-visa-mastercard-rub.html' => 'UNI',
            'https://www.bestchange.ru/ravencoin-to-visa-mastercard-rub.html' => 'RVN',
            'https://www.bestchange.ru/solana-to-visa-mastercard-rub.html' => 'SOL',
            'https://www.bestchange.ru/vechain-to-visa-mastercard-rub.html' => 'VET',
            'https://www.bestchange.ru/algorand-to-visa-mastercard-rub.html' => 'ALGO',
            'https://www.bestchange.ru/maker-to-visa-mastercard-rub.html' => 'MKR',
            'https://www.bestchange.ru/avalanche-to-visa-mastercard-rub.html' => 'AVAX',
            'https://www.bestchange.ru/yearn-finance-to-visa-mastercard-rub.html' => 'YFI',
            'https://www.bestchange.ru/terra-to-visa-mastercard-rub.html' => 'LUNA',

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

            $taskUid = $aparser->addTask('20', 'BestOUT', 'text', $ZAPROS);
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
            $content = str_replace("  ", "", $content); // Убираем пробелы
            $content = explode("\n", $content);

            // ОБНОВЛЯЕМ ТАБЛИЦУ
          //   show($content);

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
        foreach ($this->TICKERSvisaOUT as $url => $ticker)
        {
            $ARR['method'] = "VISA";
            $ARR['url'] = $url;
            $ARR['ticker'] = $ticker;
            $this->AddARRinBD($ARR, "obmenout");
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
        $table = R::findAll("obmenout");
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