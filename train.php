<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/style.css">
    <title>電光掲示板</title>
  </head>
  <body>
    <?php
      require_once("phpQuery-onefile.php");
      //html取得
      $html = file_get_contents("http://timetable.ekitan.com/train/TimeStation/149-74_D2.shtml");
      $doc = phpQuery::newDocument($html);

      //現在時刻を取得
      date_default_timezone_set('Asia/Tokyo');
      echo date("現在時刻　G:i");
      //時と分
      $hour = date("G");
      $minutes = date("i");
      $get = array();
      $firstCandidate = array();
      $secondCandidate = array();

      //デバッグ用
      if($_GET){
        $hour = $_GET['h'];
        $minutes = $_GET['t'];
        echo("設定時刻 $hour:$minutes");
      }

      //時刻表サーチ
      function get($h,$t) {
        global $doc,$html,$get;
        //スクレイピングで時刻表を取得 0:種別 1:時 2:分 3:行先
        foreach ($doc["tr:eq($h)"]->find("td")->find("ul") as $time){
          if($t < pq($time)->find("em")->text()){
            $get[0] = typeChange(pq($time)->find("span:eq(0)")->text());
            $get[1] = $doc["tr:eq($h)"]->find("th")->text();
            $get[2] = pq($time)->find("em")->text();
            $get[3] = placeChange(pq($time)->find("span:eq(1)")->text());
            $get[4] = typeChangeus (pq($time)->find("span:eq(0)")->text());
            $get[5] = placeChangeus(pq($time)->find("span:eq(1)")->text());
            return 1;
          }
        }
        return 0;
      }

      //直近の候補時間２つ取得
      function timeGet($h,$t){
        $check;
         while (true) {
           $check = get($h,$t);
           if($check == 1){
             break;
           }
           if($h < 24 ){
              $h++;
              $t = 0;
           }else{
             $h=1;
           }
         }
      }

      //置き換え処理
      function typeChange($type){
        switch ($type) {
          case '[新快]':
            return '新 快 速';
            break;
          case '[区快]':
            return '区 間 快 速';
            break;
          case '[特快]':
            return '特 別 快 速';
            break;
          default:
            return '普　　通';
            break;
        }
      }

      function placeChange($type){
        switch ($type) {
          case '垣':
            return '大　垣';
            break;
          case '岐':
            return '岐　阜';
            break;
          case '米':
            return '米　原';
            break;
          case '屋':
            return '名　古　屋';
            break;
        }
      }

      function placeChangeus($type){
        switch ($type) {
          case '垣':
            return 'Ogaki';
            break;
          case '岐':
            return 'Gifu';
            break;
          case '米':
            return 'Maibara';
            break;
          case '屋':
            return 'Nagoya';
            break;
        }
      }

      function typeChangeus($type){
        switch ($type) {
          case '[新快]':
            return 'New Rapid';
            break;
          case '[区快]':
            return 'Section Rapid';
            break;
          case '[特快]':
            return 'Special Rapid';
            break;
          default:
            return 'Local';
            break;
        }
      }
      //取得処理
      timeGet($hour-4,$minutes);
      $firstCandidate = $get;

      echo  $firstCandidate[0];
      echo  $firstCandidate[1].":";
      echo  $firstCandidate[2];
      echo  $firstCandidate[3];

      timeGet($firstCandidate[1]-4,$firstCandidate[2]);
      $secondCandidate = $get;

      echo "<br>";
      echo  $secondCandidate[0];
      echo  $secondCandidate[1].":";;
      echo  $secondCandidate[2];
      echo  $secondCandidate[3];

    ?>
    <div class="box">
      <div class="label">
        <h1>東海道本線 <span class="sub">(名古屋方面)</span></h1>
        <p>Tokaido Line (for Nagoya)</p>
      </div>
      <div class="time-box">
        <!-- 一行目 -->
        <div class="first">
          <ul class="ledText">
            <li class="type ja", id="firstja"><?php echo $firstCandidate[0]; ?></li>
            <li class="type us", id="firstus"><?php echo $firstCandidate[4]; ?></li>
            <li class="time"><?php echo $firstCandidate[1].":".$firstCandidate[2]; ?></li>
            <li  class="goal ja", id="fgja"><?php echo $firstCandidate[3]; ?></li>
            <li  class="goal us", id="fgus"><?php echo $firstCandidate[5]; ?></li>
          </ul>
        </div>

        <!-- 二行目 -->
        <div class="first">
          <ul class="ledText">
            <li class="type ja", id="secondja"><?php echo $secondCandidate[0]; ?></li>
            <li class="type us", id="secondus"><?php echo $secondCandidate[4]; ?></li>
            <li class="time"><?php echo $secondCandidate[1].":".$secondCandidate[2]; ?></li>
            <li class="goal ja", id ="sgoja"><?php echo $secondCandidate[3]; ?></li>
            <li class="goal us", id ="sgous"><?php echo $secondCandidate[5]; ?></li>
          </ul>
        </div>

        <!-- 三行目 -->
        <div class ="second">
          <?php
            switch ($firstCandidate[0]) {
              case '普　　通':
                echo '<p class="ledText"><span>この列車は、各駅に停車します。</span></p>';
                break;
              case '新 快 速':
                echo '<p class="ledText"><span>この列車は、大府・共和・金山・名古屋に停車します。</span></p>';
                break;
              case '区 間 快 速':
                echo '<p class="ledText"><span>この列車は、大府・共和・金山・名古屋に停車します。</span></p>';
                break;
              case '特 別 快 速':
                echo '<p class="ledText"><span>この列車は、金山・名古屋に停車します。</span></p>';
                break;
              default:
                break;
            }
          ?>
        </div>
      </div>

      <!-- 電光掲示板切り替え処理 -->
      <script type="text/javascript">
        var firstja = document.getElementById("firstja");
        var firstus = document.getElementById("firstus");
        var secondja = document.getElementById("secondja");
        var secondus = document.getElementById("secondus");
        var fgja = document.getElementById("fgja");
        var fgus = document.getElementById("fgus");
        var sgoja = document.getElementById("sgoja");
        var sgous = document.getElementById("sgous");
        var i = 1;

        function change(){
          if(i==1){
            firstja.style.display = 'none';
            firstja.className ='type us';
            firstus.style.display = 'inline-block';
            secondja.style.display = 'none';
            secondja.className ='type us';
            secondus.style.display = 'inline-block';

            fgja.style.display = 'none';
            fgja.className ='us';
            fgus.style.display = 'inline';
            sgoja.style.display = 'none';
            sgoja.className ='us';
            sgous.style.display = 'inline';
            i=0;
          }else{
            firstus.style.display = 'none';
            firstus.className ='type ja';
            firstja.style.display = 'inline-block';
            secondus.style.display = 'none';
            secondus.className ='type ja';
            secondja.style.display = 'inline-block';

            fgus.style.display = 'none';
            fgus.className ='ja';
            fgja.style.display = 'inline';
            sgous.style.display = 'none';
            sgous.className ='ja';
            sgoja.style.display = 'inline';
            i=1;
          }
        }
        setInterval("change()",10000);
      </script>
    </div>
  </body>
</html>
