<?PHP
    if (!isset($_SESSION['sessunameuangmuka'])) {
        header('location:index.php');
    }

    include "koneksi/connect-db.php";
//    
//    $strTotal = "";
//    $strNotif = "";
//    
//    try {
//        $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
//
//        $query = "select count(a.NOTIFICATION_ID) from notification_tab a where a.USER_ID = '".$_SESSION['sessionuseridkm']."' and a.NOTIFICATION_STATUS = 'N'";
//        $result = $db->prepare($query);
//        $result->execute();
//
//        $num = $result->rowCount();
//
//        if($num > 0) {
//            while ($row = $result->fetch(PDO::FETCH_NUM)) {
//                if($row[0]==0){
//                    $strTotal = $row[0];
//                }else{
//                    $strTotal = "<span class='label label-pill label-danger'>".$row[0]."</span>";
//                }
//            }
//        }
//    } catch (Exception $e) {
//        echo $e->getMessage();
//    }
//    
//    try {
//        $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
//
//        $query = "select a.CATEGORY_TYPE_ID, b.USER_FULL_NAME, a.NOTIFICATION_DATE, a.NOTIFICATION_TITLE, a.NOTIFICATION_CONTENT, a.NOTIFICATION_ID from notification_tab a left join user_tab b on a.USER_ID = b.USER_ID where a.USER_ID = '".$_SESSION['sessionuseridkm']."' and a.NOTIFICATION_STATUS = 'N'";
//        $result = $db->prepare($query);
//        $result->execute();
//
//        $num = $result->rowCount();
//
//        if($num > 0) {
//            while ($row = $result->fetch(PDO::FETCH_NUM)) {
//                $row[4] = base64_encode($row[4]);
//                $row[5] = base64_encode($row[5]);
//                if($row[0]==1){
//                    $strNotif = $strNotif."<div class='dropdown-menu-notif-item'>
//                                    <div class='photo'>
//                                        <img src='img/avatar-1-64.png' alt=''>
//                                    </div>
//                                    <div class='dot'></div>
//                                    <a href='view_artikel.php?id=$row[4]&stat=Y&param=$row[5]'>$row[3]</a> oleh $row[1]
//                                    <div class='color-blue-grey-lighter'>$row[2]</div>
//                                </div>";
//                }elseif($row[0]==2){
//                    $strNotif = $strNotif."<div class='dropdown-menu-notif-item'>
//                                    <div class='photo'>
//                                        <img src='img/avatar-1-64.png' alt=''>
//                                    </div>
//                                    <div class='dot'></div>
//                                    <a href='view_dokumen.php?id=$row[4]&stat=Y&param=$row[5]'>$row[3]</a> oleh $row[1]
//                                    <div class='color-blue-grey-lighter'>$row[2]</div>
//                                </div>";
//                }elseif($row[0]==3){
//                    $strNotif = $strNotif."<div class='dropdown-menu-notif-item'>
//                                    <div class='photo'>
//                                        <img src='img/avatar-1-64.png' alt=''>
//                                    </div>
//                                    <div class='dot'></div>
//                                    <a href='view_question.php?id=$row[4]&stat=Y&param=$row[5]'>$row[3]</a> oleh $row[1]
//                                    <div class='color-blue-grey-lighter'>$row[2]</div>
//                                </div>";
//                }
//            }
//        }
//    } catch (Exception $e) {
//        echo $e->getMessage();
//    }
?>

<div class="site-header-content">
    <div class="site-header-content-in">
        <div class="site-header-shown">
            <div class="dropdown dropdown-notification notif">
                <!--<a href="#"
                   class="header-alarm dropdown-toggle active"
                   id="dd-notification"
                   data-toggle="dropdown"
                   aria-haspopup="true"
                   aria-expanded="false">
                    <i class="font-icon-alarm"></i>
                </a>-->

                <!menu notifikasi-->
                <!--<div class="dropdown-menu dropdown-menu-right dropdown-menu-notif" aria-labelledby="dd-notification">
                    <div class="dropdown-menu-notif-header">
                        Notifications
                        <?PHP echo $strTotal;?>
                    </div>
                    <div class="dropdown-menu-notif-list">
                        <?PHP echo $strNotif;?>
                    </div>
                    <div class="dropdown-menu-notif-more">
                        <a href="#">See more</a>
                    </div>
                </div>-->
            </div>
        </div><!--.site-header-shown-->

    </div><!--site-header-content-in-->
</div><!--.site-header-content-->