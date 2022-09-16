<?PHP
session_start();

if (!isset($_SESSION['sessunameuangmuka'])) {
    header('location:index.php');
}

include "koneksi/connect-db.php";
include "mailer/class.PHPMailer.php";

$modeDebug = 0;
$strMessage = "";

$txtKode = base64_decode($_GET['id']);
$txtNilaiUM = "";
$txtEvaluasiNilaiUM = "";
$aging = 0;

$txtAct = "";

// GET NOMOR BERIKUTNYA
function getNomorBerikutnya($IDSerial)
{
    try {
        include "koneksi/connect-db.php";

        $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

        $result = $db->prepare("SELECT SERIAL_ID, PREFIX, START_VALUE, NEXT_VALUE, LENGTH FROM gen_serial_tab where SERIAL_ID='$IDSerial'");
        $result->execute();

        $num = $result->rowCount();

        if ($num > 0) {
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                //                    $sequence = str_pad($row[3], $row[4], '0', STR_PAD_LEFT);
                //                    
                //                    return "$row[1]/$sequence";

                $tahun = date("Y");
                $sequence = str_pad($row[3], $row[4], '0', STR_PAD_LEFT);

                return $row[0] . "-" . $tahun . "-" . $sequence;
            }
        }
    } catch (Exception $e) {
        if ($modeDebug == 0) {
            $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Oops, there is something wrong.....</div>";
        } else {
            $strMessage = $e->getMessage();
        }
    }
}

// SET NOMOR BERIKUTNYA
function setNomorBerikutnya($IDSerial)
{
    try {
        include "koneksi/connect-db.php";

        $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

        $db->beginTransaction();

        $sqlQuery = $db->prepare("UPDATE gen_serial_tab SET NEXT_VALUE = NEXT_VALUE + 1 WHERE SERIAL_ID = :id");

        $sqlQuery->bindParam(':id', $IDSerial, PDO::PARAM_STR);

        $sqlQuery->execute();

        $db->commit();
    } catch (Exception $e) {
        if ($modeDebug == 0) {
            $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Oops, there is something wrong.....</div>";
        } else {
            $strMessage = $e->getMessage();
        }
    }
}

if (isset($_POST['btnSimpan']) and $_SERVER['REQUEST_METHOD'] == "POST") {
    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

        $tglEstimasi = $_POST['tglEstimasi'];
        $tglEstimasi = explode('/', $tglEstimasi);
        $tglEstimasi = $tglEstimasi[2] . "-" . $tglEstimasi[0] . "-" . $tglEstimasi[1];

        $txtAlasan = $_POST['txtAlasan'];

        if ($tglEstimasi == "" || $txtAlasan == "") {
            $strMessage = "<div class='alert alert-error'><strong>Kolom dengan tanda bintang (*) wajib diisi</strong></div>";
        } else {
            $db->beginTransaction();

            $ppID = getNomorBerikutnya("PP");
            $stat = "Draft";

            $sqlQuery = $db->prepare("insert into t_dispensasi_tab(t_dispensasi_id, tgl_dispensasi, t_um_id, tgl_perpanjangan, alasan_perpanjangan, status_perpanjangan, user_peminta, user_penyetuju) values(:t_dispensasi_id, now(), :t_um_id, :tgl_perpanjangan, :alasan_perpanjangan, :status_perpanjangan, (select user_peminta from t_um_tab where t_um_id = :t_um_id), (select user_penyetuju from t_um_tab where t_um_id = :t_um_id))");

            $sqlQuery->bindParam(':t_dispensasi_id', $ppID, PDO::PARAM_STR);
            $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
            $sqlQuery->bindParam(':tgl_perpanjangan', $tglEstimasi, PDO::PARAM_STR);
            $sqlQuery->bindParam(':alasan_perpanjangan', $txtAlasan, PDO::PARAM_STR);
            $sqlQuery->bindParam(':status_perpanjangan', $stat, PDO::PARAM_STR);

            $sqlQuery->execute();

            $db->commit();

            if ($sqlQuery->rowCount() > 0) {
                setNomorBerikutnya("PP");

                //header('location:truck_order_overview.php');
                $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
            } else {
                $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
            }
        }
    } catch (PDOException $e) {
        if ($modeDebug == 0) {
            $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Oops, there is something wrong.....</div>";
        } else {
            $strMessage = $e->getMessage();
        }
    }
}

try {
    $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

    $query = "select * from t_um_rpt where t_um_id='$txtKode'";
    $result = $db->prepare($query);
    $result->execute();

    $num = $result->rowCount();

    if ($num > 0) {
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $txtKeperluan = $row[2];
            $txtPeriode = $row[6];
            $txtLampiran = $row[5];
            $cmbUser1 = $row[22] . " - " . $row[23] . " (" . $row[71] . " " . $row[72] . ")";
            $cmbUser2 = $row[24] . " - " . $row[25] . " (" . $row[73] . " " . $row[74] . ")";
            $cmbPengadaan1 = $row[26] . " - " . $row[27] . " (" . $row[75] . " " . $row[76] . ")";
            $cmbPengadaan2 = $row[28] . " - " . $row[29] . " (" . $row[77] . " " . $row[78] . ")";
            $txtNilaiUM = number_format($row[21], 2);
            $txtEvaluasiNilaiUM = number_format($row[30], 2);
            $cmbKeuangan1 = $row[37] . " - " . $row[38] . " (" . $row[79] . " " . $row[80] . ")";
            $cmbKeuangan2 = $row[39] . " - " . $row[40] . " (" . $row[81] . " " . $row[82] . ")";
            $notes = $row[83];
            $txtTT = $row[84];
            $cmbDiperiksa = $row[41] . " - " . $row[42];
            //$user1Status = $row[71];

            $txtAlasan = $row[96];
            $aging = $row[70];
        }
    }

    $db = null;
} catch (Exception $e) {
    if ($modeDebug == 0) {
        $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Oops, there is something wrong.....</div>";
    } else {
        $strMessage = $e->getMessage();
    }
}

$strCmbTTD1 = "";

try {
    $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

    $query = "select * from position_user_rpt WHERE position_level >= 4 and is_active = 1 order by position_level";
    $result = $db->prepare($query);
    $result->execute();

    $num = $result->rowCount();

    if ($num > 0) {
        $strCmbTTD1 = $strCmbTTD1 . "<option></option>";

        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            if ($row[0] == $cmbUser1) {
                $strCmbTTD1 = $strCmbTTD1 . "<option value='$row[0]' selected>$row[1] - $row[2]</option>";
            } else {
                $strCmbTTD1 = $strCmbTTD1 . "<option value='$row[0]'>$row[1] - $row[2]</option>";
            }
        }
    }

    $db = null;
} catch (Exception $e) {
    if ($modeDebug == 0) {
        $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Oops, there is something wrong.....</div>";
    } else {
        $strMessage = $e->getMessage();
    }
}

$strCmbTTD2 = "";

try {
    $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

    $query = "select * from position_user_rpt WHERE position_level >= 4 and is_active = 1 order by position_level";
    $result = $db->prepare($query);
    $result->execute();

    $num = $result->rowCount();

    if ($num > 0) {
        $strCmbTTD2 = $strCmbTTD2 . "<option></option>";

        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            if ($row[0] == $cmbUser2) {
                $strCmbTTD2 = $strCmbTTD2 . "<option value='$row[0]' selected>$row[1] - $row[2]</option>";
            } else {
                $strCmbTTD2 = $strCmbTTD2 . "<option value='$row[0]'>$row[1] - $row[2]</option>";
            }
        }
    }

    $db = null;
} catch (Exception $e) {
    if ($modeDebug == 0) {
        $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Oops, there is something wrong.....</div>";
    } else {
        $strMessage = $e->getMessage();
    }
}

$strCmbTTD3 = "";

try {
    $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

    $query = "select * from position_user_rpt WHERE position_level >= 4 and is_active = 1 order by position_level";
    $result = $db->prepare($query);
    $result->execute();

    $num = $result->rowCount();

    if ($num > 0) {
        $strCmbTTD3 = $strCmbTTD3 . "<option></option>";

        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            if ($row[0] == $cmbPengadaan1) {
                $strCmbTTD3 = $strCmbTTD3 . "<option value='$row[0]' selected>$row[1] - $row[2]</option>";
            } else {
                $strCmbTTD3 = $strCmbTTD3 . "<option value='$row[0]'>$row[1] - $row[2]</option>";
            }
        }
    }

    $db = null;
} catch (Exception $e) {
    if ($modeDebug == 0) {
        $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Oops, there is something wrong.....</div>";
    } else {
        $strMessage = $e->getMessage();
    }
}

$strCmbTTD4 = "";

try {
    $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

    $query = "select * from position_user_rpt WHERE position_level >= 4 and is_active = 1 order by position_level";
    $result = $db->prepare($query);
    $result->execute();

    $num = $result->rowCount();

    if ($num > 0) {
        $strCmbTTD4 = $strCmbTTD4 . "<option></option>";

        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            if ($row[0] == $cmbPengadaan2) {
                $strCmbTTD4 = $strCmbTTD4 . "<option value='$row[0]' selected>$row[1] - $row[2]</option>";
            } else {
                $strCmbTTD4 = $strCmbTTD4 . "<option value='$row[0]'>$row[1] - $row[2]</option>";
            }
        }
    }

    $db = null;
} catch (Exception $e) {
    if ($modeDebug == 0) {
        $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Oops, there is something wrong.....</div>";
    } else {
        $strMessage = $e->getMessage();
    }
}

$str = "";

try {
    $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

    $query = "select * from t_um_detail_tab where t_um_id='$txtKode' order by t_um_detail_id";
    $result = $db->prepare($query);
    $result->execute();

    $num = $result->rowCount();

    $count = 0;

    if ($num > 0) {
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $count = $count + 1;

            $str = $str . "<tr>";
            $str = $str . "<td valign='top'>" . $count . "</td>";
            $str = $str . "<td valign='top'>" . $row[2] . "</td>";
            $str = $str . "<td valign='top'>" . number_format($row[4], 2) . "</td>";
            $str = $str . "<td valign='top'>" . number_format($row[5], 2) . "</td>";
            $str = $str . "<td valign='top'>" . number_format($row[6], 2) . "</td>";
            $str = $str . "<td valign='top'>" . number_format($row[7], 2) . "</td>";
        }
    }
} catch (Exception $e) {
    if ($modeDebug == 0) {
        $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Oops, there is something wrong.....</div>";
    } else {
        $strMessage = $e->getMessage();
    }
}

if (isset($_POST['btnResend']) and $_SERVER['REQUEST_METHOD'] == "POST") {
    try {
        $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

        $query = "select status_ref from t_um_status_tab where t_um_id = '" . $txtKode . "' and um_status is null limit 1";
        //echo $query;
        $result = $db->prepare($query);
        $result->execute();

        $num = $result->rowCount();

        if ($num > 0) {
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $txtAct = $row[0];
            }
        }

        if ($txtAct == 1) {
            $query = "select user_peminta_id, user_peminta_email, tgl_um from t_um_rpt where t_um_id = '" . $txtKode . "'";

            $result = $db->prepare($query);
            $result->execute();

            $num = $result->rowCount();

            $count = 0;

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
                    $mail->Subject = "(re-send) Persetujuan Permintaan Uang Muka No : " . $txtKode;
                    $mail->Body    = "Untuk melakukan persetujuan permintaan uang muka klik alamat di bawah ini :<br><a href='$internalLink/uangmuka/persetujuan_um.php?id=" . base64_encode($txtKode) . "&param=" . base64_encode($row[2]) . "&u=" . base64_encode($row[0]) . "&act=1'>Link Internal Sistem Uang Muka</a>" . "<br><br><a href='$externalLink/uangmuka/persetujuan_um.php?id=" . base64_encode($txtKode) . "&param=" .  base64_encode($row[2]) . "&u=" .  base64_encode($row[0]) . "&act=1'>Link External Sistem Uang Muka</a>";
                    $mail->Send();

                    $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";

                    //header('location:permintaan_overview.php');
                }
            }
        } elseif ($txtAct == 2) {
            $query = "select user_penyetuju_id, user_penyetuju_email, tgl_um from t_um_rpt where t_um_id = '" . $txtKode . "'";

            $result = $db->prepare($query);
            $result->execute();

            $num = $result->rowCount();

            $count = 0;

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
                    $mail->Subject = "Persetujuan Permintaan Uang Muka No : " . $txtKode;
                    $mail->Body    = "Untuk melakukan persetujuan permintaan uang muka klik alamat di bawah ini :<br><a href='$internalLink/uangmuka/persetujuan_um.php?id=" . base64_encode($txtKode) . "&param=" . base64_encode($row[2]) . "&u=" . base64_encode($row[0]) . "&act=2'>Link Internal Sistem Uang Muka</a>" . "<br><br><a href='$externalLink/uangmuka/persetujuan_um.php?id=" . base64_encode($txtKode) . "&param=" .  base64_encode($row[2]) . "&u=" .  base64_encode($row[0]) . "&act=2'>Link External Sistem Uang Muka</a>";
                    $mail->Send();

                    $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";

                    //header('location:permintaan_overview.php');
                }
            }
        } elseif ($txtAct == 3) {
            $query = "select pengadaan_disiapkan_id, pengadaan_disiapkan_email, tgl_um from t_um_rpt where t_um_id = '" . $txtKode . "'";
            //$query = "select pengadaan_disetujui_id, pengadaan_disetujui_email, tgl_um from t_um_rpt where t_um_id = '".$txtKode."'";

            $result = $db->prepare($query);
            $result->execute();

            $num = $result->rowCount();

            $count = 0;

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
                    $mail->Subject = "Persetujuan Permintaan Uang Muka No : " . $txtKode;
                    $mail->Body    = "Untuk melakukan persetujuan permintaan uang muka klik alamat di bawah ini :<br><a href='$internalLink/uangmuka/persetujuan_um.php?id=" . base64_encode($txtKode) . "&param=" . base64_encode($row[2]) . "&u=" . base64_encode($row[0]) . "&act=3'>Link Internal Sistem Uang Muka</a>" . "<br><br><a href='$externalLink/uangmuka/persetujuan_um.php?id=" . base64_encode($txtKode) . "&param=" .  base64_encode($row[2]) . "&u=" .  base64_encode($row[0]) . "&act=3'>Link External Sistem Uang Muka</a>";
                    $mail->Send();

                    $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
                }
            }
        } elseif ($txtAct == 4) {
            $query = "select pengadaan_disetujui_id, pengadaan_disetujui_email, tgl_um from t_um_rpt where t_um_id = '" . $txtKode . "'";

            $result = $db->prepare($query);
            $result->execute();

            $num = $result->rowCount();

            $count = 0;

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
                    $mail->Subject = "Persetujuan Permintaan Uang Muka No : " . $txtKode;
                    $mail->Body    = "Untuk melakukan persetujuan permintaan uang muka klik alamat di bawah ini :<br><a href='$internalLink/uangmuka/persetujuan_um.php?id=" . base64_encode($txtKode) . "&param=" . base64_encode($row[2]) . "&u=" . base64_encode($row[0]) . "&act=4'>Link Internal Sistem Uang Muka</a>" . "<br><br><a href='$externalLink/uangmuka/persetujuan_um.php?id=" . base64_encode($txtKode) . "&param=" .  base64_encode($row[2]) . "&u=" .  base64_encode($row[0]) . "&act=4'>Link External Sistem Uang Muka</a>";
                    $mail->Send();

                    $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
                }
            }
        }
    } catch (PDOException $e) {
        if ($modeDebug == 0) {
            $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Oops, there is something wrong.....</div>";
        } else {
            $strMessage = $e->getMessage();
        }
    }
}

$strDispensasi = "";

try {
    $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

    $query = "select * from t_dispensasi_tab where t_um_id = '$txtKode'";

    $result = $db->prepare($query);
    $result->execute();

    $num = $result->rowCount();

    $count = 0;

    if ($num > 0) {
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $count = $count + 1;

            $strDispensasi = $strDispensasi . "<tr>";
            $strDispensasi = $strDispensasi . "<td valign='top'>" . $row[0] . "</td>";
            $strDispensasi = $strDispensasi . "<td valign='top'>" . $row[1] . "</td>";
            $strDispensasi = $strDispensasi . "<td valign='top'>" . $row[3] . "</td>";
            $strDispensasi = $strDispensasi . "<td valign='top'>" . $row[4] . "</td>";
            $strDispensasi = $strDispensasi . "<td valign='top'>" . $row[5] . "</td>";

            $row[0] = base64_encode($row[0]);

            if ($row[5] == "Draft") {
                $strDispensasi = $strDispensasi . "<td class='center'>
                                    <a class='btn btn-success' href='permintaan_view_aging_proposed.php?id=$row[0]'>
                                            <i class='icon-edit icon-white'></i>  
                                            Proposed                                          
                                    </a>
                            </td>";
            } else {
                $strDispensasi = $strDispensasi . "<td class='center'></td>";
            }
        }
    }
} catch (Exception $e) {
    if ($modeDebug == 0) {
        $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Oops, there is something wrong.....</div>";
    } else {
        $strMessage = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>

<head lang="en">
    <?PHP
    include "header.php";
    ?>

    <link rel="stylesheet" href="css/separate/vendor/bootstrap-daterangepicker.min.css">
</head>

<body class="with-side-menu">

    <header class="site-header">
        <div class="container-fluid">

            <!logo startui-->
                <a href="#" class="site-logo">
                    <!--<img class="hidden-md-down" src="img/logo-2.png" alt="">
	            <img class="hidden-lg-down" src="img/logo-2-mob.png" alt="">-->
                </a>

                <!toggle show hide menu-->
                    <button id="show-hide-sidebar-toggle" class="show-hide-sidebar">
                        <span>toggle menu</span>
                    </button>

                    <button class="hamburger hamburger--htla">
                        <span>toggle menu</span>
                    </button>
                    <?PHP include "menu_up.php"; ?>
        </div>
        <!--.container-fluid-->
    </header>
    <!--.site-header-->

    <div class="mobile-menu-left-overlay"></div>
    <?PHP include "menu_left.php"; ?>

    <div class="page-content">
        <?PHP echo $strMessage; ?>
        <div class="container-fluid">
            <div class="box-typical box-typical-padding">
                <h2 class="with-border">View Uang Muka : <?PHP echo $txtKode . " (Aging : " . $aging . " hari)"; ?></h2>
                <form id="frmPermintaan" action="" method="post" enctype="multipart/form-data">
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Keperluan *</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><?PHP echo $txtKeperluan; ?></p>
                        </div>
                        <label class="col-sm-2 form-control-label">Periode *</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><?PHP echo $txtPeriode; ?></p>
                        </div>
                        <label class="col-sm-2 form-control-label">Nilai UM *</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><?PHP echo $txtNilaiUM; ?></p>
                        </div>
                        <label class="col-sm-2 form-control-label">Evaluasi Nilai UM *</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><?PHP echo $txtEvaluasiNilaiUM; ?></p>
                        </div>
                        <label class="col-sm-2 form-control-label">Lampiran</label>
                        <div class="col-sm-10">
                            <div class="form-group">
                                <div class="input-group">
                                    <?PHP if ($txtLampiran == "") {
                                        echo "<span class='color-red'>Lampiran belum diupload !!</span>";
                                    } else {
                                        echo "<a href='files/$txtLampiran' target='_blank'>" . "Download Lampiran</a>";
                                    } ?>
                                </div>
                            </div>
                        </div>
                        <label class="col-sm-2 form-control-label">Notes Pengambilan UM</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><?PHP echo $notes; ?></p>
                        </div>
                        <label class="col-sm-2 form-control-label">Tanda Terima</label>
                        <div class="col-sm-10">
                            <div class="form-group">
                                <div class="input-group">
                                    <?PHP if ($txtTT == "") {
                                        echo "<span class='color-red'>Tanda Terima belum diupload !!</span>";
                                    } else {
                                        echo "<a href='files/$txtTT' target='_blank'>" . "Download Tanda Terima</a>";
                                    } ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="box-typical box-typical-padding">
                        <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Rincian</th>
                                    <th>Qty</th>
                                    <th>Harga</th>
                                    <th>Qty Evaluasi</th>
                                    <th>Harga Evaluasi</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>No</th>
                                    <th>Rincian</th>
                                    <th>Qty</th>
                                    <th>Harga</th>
                                    <th>Qty Evaluasi</th>
                                    <th>Harga Evaluasi</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                <?PHP echo $str; ?>
                            </tbody>
                        </table>
                    </div>

                    <h5 class="with-border">User / Pemohon</h5>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Yang Meminta *</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><?PHP echo $cmbUser1; ?></p>
                        </div>
                        <label class="col-sm-2 form-control-label">Disetujui *</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><?PHP echo $cmbUser2; ?></p>
                        </div>
                    </div>
					<!--
                    <h5 class="with-border">Divisi Pengadaan</h5>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Disiapkan *</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><?PHP echo $cmbPengadaan1; ?></p>
                        </div>
                        <label class="col-sm-2 form-control-label">Disetujui *</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><?PHP echo $cmbPengadaan2; ?></p>
                        </div>
                    </div>
					-->
                    <h5 class="with-border">Keuangan</h5>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Diperiksa *</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><?PHP echo $cmbDiperiksa; ?></p>
                        </div>
                        <label class="col-sm-2 form-control-label">Disiapkan *</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><?PHP echo $cmbKeuangan1; ?></p>
                        </div>
                        <label class="col-sm-2 form-control-label">Disetujui *</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><?PHP echo $cmbKeuangan2; ?></p>
                        </div>
                    </div>

                    <h5 class="with-border"><span class="color-red">Alasan Reject (Hanya Terisi Apabila Status Reject)</span></h5>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label"><span class="color-red">Alasan Reject</span></label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><span class="color-red"><?PHP echo $txtAlasan; ?></span></p>
                        </div>
                    </div>
            </div>
            <?PHP
            if ($aging >= 30) {
                echo "<div class='box-typical box-typical-padding'>
                                <form id='frmPerpanjangan' action='' method='post' enctype='multipart/form-data'>
                                    <div class='form-group row'>
                                        <label class='col-sm-2 form-control-label'>Estimasi Tgl PJUM *</label>
                                        <div class='col-sm-10'>
                                            <div class='form-group'>
                                                <div class='input-group date'>
                                                    <input name='tglEstimasi' id='tglEstimasi' type='text' class='form-control'>
                                                    <div class='input-group-addon'>
                                                        <span class='font-icon font-icon-calend'></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <label class='col-sm-2 form-control-label'>Alasan *</label>
                                        <div class='col-sm-10'>
                                            <p class='form-control-static'><input type='text' class='form-control' name='txtAlasan' id='txtAlasan'></p>
                                        </div>
                                    </div>
                                    <div class='form-group row'>
                                        <div class='col-sm-2'>
                                            <button type='submit' class='btn btn-inline' name='btnSimpan' id='btnSimpan'>Ajukan Perpanjangan PJUM</button>
                                        </div>
                                    </div>
                                </form></div>";
            }
            ?>

            <div class="box-typical box-typical-padding">
                <h2 class="with-border">Perpanjangan PJUM</h2>
                <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <!--<th>No</th>-->
                            <th>Kode</th>
                            <th>Tgl Pengajuan</th>
                            <th>Estimasi Perpanjangan PJUM</th>
                            <th>Alasan</th>
                            <th>Status</th>
                            <th>Proposed</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <!--<th>No</th>-->
                            <th>Kode</th>
                            <th>Tgl Pengajuan</th>
                            <th>Estimasi Perpanjangan PJUM</th>
                            <th>Alasan</th>
                            <th>Status</th>
                            <th>Proposed</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?PHP echo $strDispensasi; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!--.page-content-->

    <script src="js/lib/jquery/jquery-3.2.1.min.js"></script>
    <script src="js/lib/popper/popper.min.js"></script>
    <script src="js/lib/tether/tether.min.js"></script>
    <script src="js/lib/bootstrap/bootstrap.min.js"></script>
    <script src="js/plugins.js"></script>
    <script src="js/lib/select2/select2.full.min.js"></script>
    <script src="js/lib/autosize/autosize.min.js"></script>

    <script>
        $(function() {
            autosize($('textarea[data-autosize]'));
        });
    </script>

    <script type="text/javascript" src="js/lib/moment/moment-with-locales.min.js"></script>
    <script src="js/lib/daterangepicker/daterangepicker.js"></script>

    <script>
        $(function() {
            $('#tglEstimasi').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true
            });
        });
    </script>

    <script src="js/app.js"></script>
</body>

</html>