<?php
ini_set('max_execution_time', '0');

include "koneksi/connect-db.php";
include("mailer/class.PHPMailer.php");

$modeDebug = 1;
$strPengumuman = "";
$strSyaratUmum = "";
$strMessage = "";

if (isset($_POST['btnForgot']) and $_SERVER['REQUEST_METHOD'] == "POST") {
    $strMessage = "";

    try {
        $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = "select a.* from blast_email a where a.status = 0 order by a.seq_no";
        $result = $db->prepare($query);
        $result->execute();

        $num = $result->rowCount();

        if ($num > 0) {
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $mail = new PHPMailer();
                $mail->IsSMTP();
                $mail->Host = "mail.ptkbs.co.id";
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = "tls";
                $mail->Port     = 587;
                $mail->Username = "developer@ptkbs.co.id";
                $mail->Password = "Cigading123";
                $mail->AddAddress($row[1], $row[1]);
                $mail->From = "developer@ptkbs.co.id";
                $mail->FromName = "Sistem Uang Muka";
                $mail->IsHTML(true);
                $mail->Subject = "Username & Password Sistem Uang Muka Online";
                $mail->Body    = "Berikut disampaikan username & password untuk akses Sistem Uang Muka Online PT. Krakatau Bandar Samudera :<br><br>Username : " . $row[4] . "<br>Password : " . $row[2] . "<br>Link Sistem Uang Muka (internal KBS) : <a href='$internalLink/uangmuka'>$internalLink/uangmuka</a><br>Link Sistem Uang Muka (external KBS) : <a href='$externalLink/uangmuka'>$externalLink/uangmuka</a><br><br>Untuk alasan keamanan, harap segera merubah password yang telah kami kirimkan di atas.<br>Terima kasih.";
                $mail->Send();

                $db->beginTransaction();
                $stat = 1;
                $sqlQuery = $db->prepare("update blast_email set status = :status where email = :email");

                $sqlQuery->bindParam(':status', $stat, PDO::PARAM_STR);
                $sqlQuery->bindParam(':email', $row[1], PDO::PARAM_STR);

                $sqlQuery->execute();
                //echo var_export($sqlQuery->errorInfo());
                $db->commit();
            }
        }

        $db = null;
    } catch (PDOException $e) {
        if ($modeDebug == 0) {
            $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Oops, there is something wrong.....</div>";
        } else {
            $strMessage = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head lang="en">
    <?PHP
    include "header.php";
    ?>
</head>

<body class="horizontal-navigation">
    <header class="site-header">
        <div class="container-fluid">
            <a href="#" class="site-logo">
                <!--<img class="hidden-md-down" src="img/logo-2.png" alt="">
                    <img class="hidden-lg-down" src="img/logo-2-mob.png" alt="">-->
            </a>

            <button id="show-hide-sidebar-toggle" class="show-hide-sidebar">
                <span>toggle menu</span>
            </button>

            <button class="hamburger hamburger--htla">
                <span>toggle menu</span>
            </button>
        </div>
        <!--.container-fluid-->
    </header>
    <!--.site-header-->

    <!--<div class="mobile-menu-left-overlay"></div>
        <ul class="main-nav nav nav-inline">
            <li class="nav-item">
                <a class="nav-link" href="http://cigadingport.com" target="_blank">Web PT. KBS</a>
            </li>
        </ul>-->

    <div class="page-content">
        <?PHP echo $strMessage; ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-6">
                    <div class="box-typical box-typical-padding">
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="tab-content no-styled profile-tabs">
                        <section class="tabs-section">
                            <div class="tabs-section-nav tabs-section-nav-left">
                                <ul class="nav" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" href="#tabs-1-tab-1" id="tabs-1-tab-1-id" role="tab" data-toggle="tab">
                                            <span class="nav-link-in">Blasting Email</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <!--.tabs-section-nav-->
                            <div class="tab-content no-styled profile-tabs">
                                <!-- tab yang default active kasih atribut active -->
                                <div role="tabpanel" class="tab-pane active" id="tabs-1-tab-1">
                                    <section class="box-typical box-typical-padding">
                                        <form id="frmLogin" class="sign-box" action="" method="post" enctype="multipart/form-data">
                                            <button type="submit" class="btn" name="btnForgot" id="btnForgot">Blasting Email</button>
                                        </form>
                                    </section>
                                </div>
                                <!--.tab-pane-->
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
        <!--.container-fluid-->
    </div>
    <!--.page-content-->

    <script src="js/lib/jquery/jquery-3.2.1.min.js"></script>
    <script src="js/lib/popper/popper.min.js"></script>
    <script src="js/lib/tether/tether.min.js"></script>
    <script src="js/lib/bootstrap/bootstrap.min.js"></script>
    <script src="js/plugins.js"></script>
    <script type="text/javascript" src="js/lib/jqueryui/jquery-ui.min.js"></script>
</body>

</html>