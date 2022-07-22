<div class="card">
    <div class="card-header bg-dark text-white header-elements-inline">
        <h5 class="card-title">РАБОЧАЯ ОБЛАСТЬ</h5>

    </div>



    <div class="card-body">


        Направление: <?=$_GET['type']?> <br>


        <? if($_GET['type'] == "enter"):?>
            <h3>ШАГ-1: СОЗДАЕМ СДЕЛКУ В ОБМЕННИКЕ</h3>
            ОТДАЕМ: <?=$Scanload['method']?><br>
            ПОЛУЧАЕМ: <?=$Scanload['ticker']?><br>

            <a href="<?=$Scanload['redirect']?>" type="button" class="btn btn-success" target="_blank"><b>Перейти в обменник</b></a><br>
        <br>
            <a href="<?=$Scanload['url']?>"  target="_blank"><b>Еще варианты на BestChange</b></a><br>

        <?php endif;?>




        <? if($_GET['type'] == "exit"):?>

            <h3>ШАГ-1: СОЗДАЕМ СДЕЛКУ В ОБМЕННИКЕ</h3>
            ОТДАЕМ: <?=$Scanload['ticker']?><br>
            ПОЛУЧАЕМ: <?=$Scanload['method']?><br>

            <a href="<?=$Scanload['redirect']?>" type="button" class="btn btn-success" target="_blank"><b>Перейти в обменник</b></a><br>
        <br>
            <a href="<?=$Scanload['url']?>"  target="_blank"><b>Еще варианты на BestChange</b></a><br>


        <?php endif;?>

        <hr>

        <a href="/panel/" type="button" class="btn btn-danger"><i class="icon-alert mr-2"></i>НАЗАД</a> <br>



        <?php

        show($_GET);

        ?>








    </div>



</div>

