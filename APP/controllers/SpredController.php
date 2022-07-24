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
    public $minumumspred = 0.3;


    // ТЕХНИЧЕСКИЕ ПЕРЕМЕННЫЕ
    public function indexAction()
    {

        $this->layaout = false;

      //  date_default_timezone_set('UTC');
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
//        $this->EXCHANGES[] = "Poloniex";
 //       $this->EXCHANGES[] = "Gateio";
 //       $this->EXCHANGES[] = "Huobi";
   //     $this->EXCHANGES[] = "Ftx";
   //     $this->EXCHANGES[] = "Exmo";
  //      $this->EXCHANGES[] = "Kucoin";
  //      $this->EXCHANGES[] = "Okex";

        exit("-_-.!.");

      //  echo "<h2><font color='#8b0000'>СВЯЗКИ USDT-EXCHANGE-USDT</font></h2>";
        $Base = "USDT";

        $DATA = [];

        echo "<h2>VER 3.0</h2>";





        echo "<hr>";



//        $this->set(compact(''));

    }




    private function LoadTickersBD($type, $method)
    {

        $table = [];
        if ($type == "IN") $table = R::findAll("obmenin", 'WHERE method=?', [$method]);
        if ($type == "OUT") $table = R::findAll("obmenout",'WHERE method=?', [$method]);

        return $table;
    }

    private function GetTickerText($exchange){

        $file = file_get_contents(WWW."/Ticker".$exchange.".txt");     // Открыть файл data.json
        $MASSIV = json_decode($file,TRUE);              // Декодировать в массив
        return $MASSIV;

    }






}
?>