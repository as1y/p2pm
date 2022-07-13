<?php
namespace APP\controllers;
use APP\core\Cache;
use APP\models\Addp;
use APP\models\Panel;
use APP\core\base\Model;
use RedBeanPHP\R;

class ParsetickersController extends AppController {
    public $layaout = 'PANEL';
    public $BreadcrumbsControllerLabel = "Панель управления";
    public $BreadcrumbsControllerUrl = "/panel";


    public $sleep = 5;
    public $type = "TICKERS";

    // ТЕХНИЧЕСКИЕ ПЕРЕМЕННЫЕ
    public function indexAction()
    {

        $this->layaout = false;
        $Panel =  new Panel();

        $this->ControlTrek();
        $this->StartTrek();

        echo "<h2>ПАРСИНГ ТИКЕРОВ</h2>";

        // Перезаписывать на диск???

        //БИНАНС
        $exchangeBinance = new \ccxt\binance (array ('timeout' => 30000));
        $DATA = $exchangeBinance->fetch_tickers();
        $this->WriteTickers("Binance", $DATA);


        $exchangePoloniex = new \ccxt\poloniex (array ('timeout' => 30000));
        $DATA = $exchangePoloniex->fetch_tickers();
        $this->WriteTickers("Poloniex", $DATA);

        $exchangeGateio = new \ccxt\poloniex (array ('timeout' => 30000));
        $DATA = $exchangeGateio->fetch_tickers();
        $this->WriteTickers("Gateio", $DATA);


        // Проверка наличие файла


        //БИНАНС
        $sleep = rand($this->sleep, $this->sleep*2);
        sleep($sleep);


        // Обновление



        $this->StopTrek();




//        $this->set(compact(''));

    }


    private function ControlTrek(){
        $tbl = R::findOne("trekcontrol", "WHERE type =?", [$this->type]);
        if (empty($tbl)) return true;
        if ($tbl['work'] == 1)
        {
            echo "Процесс в работе. Новый не запускаем<br>";
            exit();
        }
        return true;
    }
    private function StartTrek(){
        $tbl = R::findOne("trekcontrol", "WHERE type =?", [$this->type]);
        if (empty($tbl)){

            $ARR['type'] = $this->type;
            $ARR['work'] = 1;
            $this->AddARRinBD($ARR, "trekcontrol");
            return true;
        }

        $tbl->work = 1;
        R::store($tbl);
        return true;
    }
    private function StopTrek(){
        $tbl = R::findOne("trekcontrol", "WHERE type =?", [$this->type]);
        $tbl->work = 0;
        R::store($tbl);
        exit();
    }

    private function WriteTickers($exchangename, $Tickers){



        if (!file_exists("Ticker".$exchangename.".txt"))
        {
            $fd = fopen("Ticker".$exchangename.".txt", 'w') or die("не удалось создать файл");
            fwrite($fd, "");
            fclose($fd);
        }

        $data = json_encode($Tickers);

        file_put_contents("Ticker".$exchangename.".txt", $data);

        return true;

    }









}
?>