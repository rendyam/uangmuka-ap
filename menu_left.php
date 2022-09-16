<?PHP
    if (!isset($_SESSION['sessunameuangmuka'])) {
        header('location:index.php');
    }

    include "koneksi/connect-db.php";

    $val = 0;
    $valUM = 0;
    $valPJUM = 0;
    $valperpanjangan = 0;
    
    try {
        $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

        $query = "SELECT COUNT(a.t_um_id) FROM t_um_status_tab a WHERE a.um_position_user_id = ".$_SESSION['sessiduangmuka']." AND a.tgl_status IS null";
        $result = $db->prepare($query);
        $result->execute();     

        $num = $result->rowCount();

        if($num > 0) {
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $valUM = $row[0];
            }
        }
    } catch (Exception $e) {
        if($modeDebug==0){
            $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Oops, there is something wrong.....</div>";
        }else{
            $strMessage = $e->getMessage();
        }
    }

    try {
        $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

        $query = "SELECT COUNT(a.t_pjum_id) FROM t_pjum_status_tab a WHERE a.pjum_position_user_id = ".$_SESSION['sessiduangmuka']." AND a.tgl_status IS null";
        $result = $db->prepare($query);
        $result->execute();     

        $num = $result->rowCount();

        if($num > 0) {
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $valPJUM = $row[0];
            }
        }
    } catch (Exception $e) {
        if($modeDebug==0){
            $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Oops, there is something wrong.....</div>";
        }else{
            $strMessage = $e->getMessage();
        }
    }
    
    try {
        $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

        $query = "SELECT COUNT(a.t_dispensasi_id) FROM t_dispensasi_status_tab a WHERE a.dispensasi_position_user_id = ".$_SESSION['sessiduangmuka']." AND a.tgl_status IS null";
        $result = $db->prepare($query);
        $result->execute();     

        $num = $result->rowCount();

        if($num > 0) {
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $valperpanjangan = $row[0];
            }
        }
    } catch (Exception $e) {
        if($modeDebug==0){
            $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Oops, there is something wrong.....</div>";
        }else{
            $strMessage = $e->getMessage();
        }
    }
    
    $val = $valUM + $valPJUM + $valperpanjangan;
?>

<nav class="side-menu">
    <div class="side-menu-avatar">
        <div class="avatar-preview avatar-preview-100">
            <img src="img/avatar-1-256.png" alt="">
        </div>
        <center>
            <span class="lbl"><?PHP echo $_SESSION['sessunameuangmuka'];?></span><br>
        </center>
    </div>
    <ul class="side-menu-list">
        <li class="brown">
            <a href="home.php">
                <i class="font-icon glyphicon glyphicon-home"></i>
                <span class="lbl">Home</span>
            </a>
        </li>
        <!kalo menunya lagi open kasih atribut opened-->
        <li class="blue-dirty">
            <a href="persetujuan_overview.php">
                <i class="font-icon glyphicon glyphicon-th"></i>
                <span class='lbl'>Persetujuan</span>
                <span class="label label-custom label-pill label-danger"><?PHP echo $val;?></span>
            </a>
        </li>
        <li class="blue-dirty">
            <a href="aging_um_user.php">
                <i class="font-icon glyphicon glyphicon-th"></i>
                <span class='lbl'>Aging UM</span>
            </a>
        </li>
        <li class="blue-dirty">
            <a href="permintaan_overview.php">
                <i class="font-icon glyphicon glyphicon-th"></i>
                <span class='lbl'>Permintaan UM</span>
            </a>
        </li>
        <?PHP
            if($_SESSION['sessevaluangmuka']==1){
                echo "<li class='blue-dirty'>
                        <a href='evaluasi_overview.php'>
                            <i class='font-icon glyphicon glyphicon-th'></i>
                            <span class='lbl'>Evaluasi Permintaan UM</span>
                        </a>
                    </li>";
            }
        ?>
        <?PHP
            if($_SESSION['sesspencairanuangmuka']==1){
                echo "<li class='blue-dirty'>
                        <a href='pencairan_overview.php'>
                            <i class='font-icon glyphicon glyphicon-th'></i>
                            <span class='lbl'>Pencairan UM</span>
                        </a>
                    </li>";
            }
        ?>
        <li class="blue-dirty">
            <a href="pertanggungjawaban_overview.php">
                <i class="font-icon glyphicon glyphicon-th"></i>
                <span class='lbl'>Pertanggungjawaban UM</span>
            </a>
        </li>
        <?PHP
            if($_SESSION['sessevaluangmuka']==1){
                echo "<li class='blue-dirty'>
                        <a href='evaluasi_pjum_overview.php'>
                            <i class='font-icon glyphicon glyphicon-th'></i>
                            <span class='lbl'>Evaluasi PJUM</span>
                        </a>
                    </li>";
            }
        ?>
        <?PHP
            if($_SESSION['sessuangmukaro']==1){
                echo "<li class='blue-dirty'>
                        <a href='permintaan_overview_ro.php'>
                            <i class='font-icon glyphicon glyphicon-th'></i>
                            <span class='lbl'>Semua Permintaan UM (View Only)</span>
                        </a>
                    </li>";
            }
        ?>
        <?PHP
            if($_SESSION['sessuangmukaro']==1){
                echo "<li class='blue-dirty'>
                        <a href='pertanggungjawaban_overview_ro.php'>
                            <i class='font-icon glyphicon glyphicon-th'></i>
                            <span class='lbl'>Semua PJUM (View Only)</span>
                        </a>
                    </li>";
            }
        ?>
        <?PHP
            if($_SESSION['sessuangmukaro']==1){
                echo "<li class='blue-dirty'>
                        <a href='aging_um.php'>
                            <i class='font-icon glyphicon glyphicon-th'></i>
                            <span class='lbl'>Aging UM</span>
                        </a>
                    </li>";
            }
        ?>
        <li class="red">
            <a href="logout.php">
                <i class="font-icon glyphicon glyphicon-log-out"></i>
                <span class="lbl">Logout</span>
            </a>
        </li>
    </ul>
    
    <?PHP include "menu_left_list_app.php";?>
</nav><!--.side-menu-->