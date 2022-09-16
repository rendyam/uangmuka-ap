<?PHP
session_start();

if (!isset($_SESSION['sessunameuangmuka'])) {
    header('location:index.php');
}

include "koneksi/connect-db.php";
include "f_setter_getter_serial.php";

$modeDebug = 0;
$strMessage = "";

$txtKodeUM = base64_decode($_GET['id']);
$txtLampiran = "";
$txtStatProposed = "";
$txtNilaiUM = "";
$txtEvaluasiNilaiUM = "";

// TAMBAH
if (isset($_POST['btnSimpan']) and $_SERVER['REQUEST_METHOD'] == "POST") {
    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

        $txtLampiran = $_FILES['txtLampiran2']['name'];
        $cmbUser1 = $_POST['cmbUser1'];
        $cmbUser2 = $_POST['cmbUser2'];
        $cmbPengadaan1 = "";
        $cmbPengadaan2 = "";
        $txtKode = getNomorBerikutnya("PJUM");
        $txtStatus = "Draft";
        $note_reject = "";

        if ($txtLampiran == "" || $cmbUser1 == "" || $cmbUser2 == "") {
            $strMessage = "<div class='alert alert-error'><strong>Kolom dengan tanda bintang (*) wajib diisi</strong></div>";
        } else {
            if ($txtLampiran == '') {
                $db->beginTransaction();

                $sqlQuery = $db->prepare("insert into t_pjum_tab(t_pjum_id, tgl_pjum, user_peminta, user_penyetuju, pengadaan_disiapkan, pengadaan_disetujui, status_um, t_um_id, user_pembuat, note_reject) values(:t_pjum_id, now(), :user_peminta, :user_penyetuju, :pengadaan_disiapkan, :pengadaan_disetujui, :status_um, :t_um_id, :user_pembuat, :note_reject)");

                $sqlQuery->bindParam(':t_pjum_id', $txtKode, PDO::PARAM_STR);
                $sqlQuery->bindParam(':user_peminta', $cmbUser1, PDO::PARAM_STR);
                $sqlQuery->bindParam(':user_penyetuju', $cmbUser2, PDO::PARAM_STR);
                $sqlQuery->bindParam(':pengadaan_disiapkan', $cmbPengadaan1, PDO::PARAM_STR);
                $sqlQuery->bindParam(':pengadaan_disetujui', $cmbPengadaan2, PDO::PARAM_STR);
                $sqlQuery->bindParam(':status_um', $txtStatus, PDO::PARAM_STR);
                $sqlQuery->bindParam(':t_um_id', $txtKodeUM, PDO::PARAM_STR);
                $sqlQuery->bindParam(':user_pembuat', $_SESSION['sessiduangmuka'], PDO::PARAM_STR);
                $sqlQuery->bindParam(':note_reject', $note_reject, PDO::PARAM_STR);

                $sqlQuery->execute();

                $db->commit();

                if ($sqlQuery->rowCount() > 0) {
                    setNomorBerikutnya("PJUM");

                    header('location:pjum_edit.php?id=' . base64_encode($txtKode));

                    //header('location:home.php');
                    //$strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
                } else {
                    $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
                }
            } else {
                $path = "files/";

                $name = $_FILES['txtLampiran2']['name'];
                $size = $_FILES['txtLampiran2']['size'];

                if (strlen($name)) {
                    //if($size > 250000){
                    //    $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Ukuran KTP terlalu besar !!</div>";
                    //}else{
                    //list($txt, $ext) = explode(".", $name);

                    //$actual_image_name = "KTP_".$kdPelamar.".".strtolower($ext);

                    $actual_image_name = $txtKode . "_" . $name;
                    $tmp = $_FILES['txtLampiran2']['tmp_name'];

                    if (move_uploaded_file($tmp, $path . $actual_image_name)) {
                        $db->beginTransaction();

                        $sqlQuery = $db->prepare("insert into t_pjum_tab(t_pjum_id, tgl_pjum, user_peminta, user_penyetuju, pengadaan_disiapkan, pengadaan_disetujui, status_pjum, t_um_id, lampiran_pjum, user_pembuat) values(:t_pjum_id, now(), :user_peminta, :user_penyetuju, :pengadaan_disiapkan, :pengadaan_disetujui, :status_pjum, :t_um_id, :lampiran_pjum, :user_pembuat)");

                        $sqlQuery->bindParam(':t_pjum_id', $txtKode, PDO::PARAM_STR);
                        //echo $txtKode.$cmbUser1.$cmbUser2.$cmbPengadaan1.$cmbPengadaan2.;
                        $sqlQuery->bindParam(':user_peminta', $cmbUser1, PDO::PARAM_STR);
                        $sqlQuery->bindParam(':user_penyetuju', $cmbUser2, PDO::PARAM_STR);
                        $sqlQuery->bindParam(':pengadaan_disiapkan', $cmbPengadaan1, PDO::PARAM_STR);
                        $sqlQuery->bindParam(':pengadaan_disetujui', $cmbPengadaan2, PDO::PARAM_STR);
                        $sqlQuery->bindParam(':status_pjum', $txtStatus, PDO::PARAM_STR);
                        $sqlQuery->bindParam(':t_um_id', $txtKodeUM, PDO::PARAM_STR);
                        $sqlQuery->bindParam(':lampiran_pjum', $actual_image_name, PDO::PARAM_STR);
                        $sqlQuery->bindParam(':user_pembuat', $_SESSION['sessiduangmuka'], PDO::PARAM_STR);

                        $sqlQuery->execute();

                        $db->commit();

                        if ($sqlQuery->rowCount() > 0) {
                            setNomorBerikutnya("PJUM");

                            header('location:pjum_edit.php?id=' . base64_encode($txtKode));
                        } else {
                            $strMessage = "<div class='alert alert-danger'><strong>-Data gagal disimpan</strong></div>";
                        }
                    } else {
                        $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Upload lampiran gagal. Ulangi proses upload lampiran !!</div>";
                    }
                    //}
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

if (isset($_POST['btnPropose']) and $_SERVER['REQUEST_METHOD'] == "POST") {
    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

        $query = "select user_peminta_id, user_peminta_email, tgl_um from t_um_rpt where t_um_id = '" . $txtKode . "'";
        $result = $db->prepare($query);
        $result->execute();

        $num = $result->rowCount();

        $count = 0;

        if ($num > 0) {
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $db->beginTransaction();

                $sqlQuery = $db->prepare("insert t_um_status_tab(t_um_id, um_position_user_id) values(:t_um_id, :um_position_user_id)");

                $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
                $sqlQuery->bindParam(':um_position_user_id', $row[0], PDO::PARAM_STR);

                $sqlQuery->execute();

                $db->commit();

                if ($sqlQuery->rowCount() > 0) {
                    $db->beginTransaction();

                    $txtStatProposed = "Proposed";

                    $sqlQuery = $db->prepare("update t_um_tab set status_um = :status_um where t_um_id = :t_um_id");

                    $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':status_um', $txtStatProposed, PDO::PARAM_STR);

                    $sqlQuery->execute();

                    $db->commit();

                    if ($sqlQuery->rowCount() > 0) {
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
                        $mail->Body    = "Untuk melakukan persetujuan permintaan uang muka klik alamat di bawah ini :<br><a href='$internalLink/uangmuka/persetujuan_um.php?id=" . base64_encode($txtKode) . "&param=" . base64_encode($row[2]) . "&u=" . base64_encode($row[0]) . "&act=1'>Link Internal Sistem Uang Muka</a>" . "<br><br><a href='$externalLink/uangmuka/persetujuan_um.php?id=" . base64_encode($txtKode) . "&param=" .  base64_encode($row[2]) . "&u=" .  base64_encode($row[0]) . "&act=1'>Link External Sistem Uang Muka</a>";
                        $mail->Send();
                    }

                    header('location:permintaan_overview.php');
                } else {
                    $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
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

if (isset($_POST['btnCancel']) and $_SERVER['REQUEST_METHOD'] == "POST") {
    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

        $txtStatus = "Cancelled";

        $db->beginTransaction();

        $sqlQuery = $db->prepare("update t_um_tab set status_um = :status_um where t_um_id = :t_um_id");

        $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
        $sqlQuery->bindParam(':status_um', $txtStatus, PDO::PARAM_STR);

        $sqlQuery->execute();

        $db->commit();

        if ($sqlQuery->rowCount() > 0) {
            header('location:permintaan_overview.php');
        } else {
            $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
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
    $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

    $query = "select * from t_um_rpt where t_um_id='$txtKodeUM'";
    $result = $db->prepare($query);
    $result->execute();

    $num = $result->rowCount();

    if ($num > 0) {
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $txtKeperluan = $row[2];
            $txtPeriode = $row[6];
            $txtLampiran = $row[5];
            $cmbUser1 = $row[7];
            $cmbUser2 = $row[8];
            $cmbPengadaan1 = $row[9];
            $cmbPengadaan2 = $row[10];
            $txtNilaiUM = number_format($row[21], 2);
            $txtEvaluasiNilaiUM = number_format($row[30], 2);
            $txtKelebihan = $row[85];
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
    $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

    $query = "select * from position_user_rpt WHERE position_level >= 4 and is_active = 1 order by position_level";
    $result = $db->prepare($query);
    $result->execute();

    $num = $result->rowCount();

    if ($num > 0) {
        $strCmbTTD1 = $strCmbTTD1 . "<option></option>";

        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            //if($row[0]==$cmbUser1){
            //    $strCmbTTD1 = $strCmbTTD1."<option value='$row[0]' selected>$row[1] - $row[2]</option>";
            //}else{
            $strCmbTTD1 = $strCmbTTD1 . "<option value='$row[0]'>$row[1] - $row[2]</option>";
            //}
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
    $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

    $query = "select * from position_user_rpt WHERE position_level >= 4 and is_active = 1 order by position_level";
    $result = $db->prepare($query);
    $result->execute();

    $num = $result->rowCount();

    if ($num > 0) {
        $strCmbTTD2 = $strCmbTTD2 . "<option></option>";

        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            //if($row[0]==$cmbUser2){
            //    $strCmbTTD2 = $strCmbTTD2."<option value='$row[0]' selected>$row[1] - $row[2]</option>";
            //}else{
            $strCmbTTD2 = $strCmbTTD2 . "<option value='$row[0]'>$row[1] - $row[2]</option>";
            //}
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
    $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

    $query = "select * from position_user_rpt WHERE position_level >= 4 and is_active = 1 order by position_level";
    $result = $db->prepare($query);
    $result->execute();

    $num = $result->rowCount();

    if ($num > 0) {
        $strCmbTTD3 = $strCmbTTD3 . "<option></option>";

        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            //if($row[0]==$cmbPengadaan1){
            //    $strCmbTTD3 = $strCmbTTD3."<option value='$row[0]' selected>$row[1] - $row[2]</option>";
            //}else{
            $strCmbTTD3 = $strCmbTTD3 . "<option value='$row[0]'>$row[1] - $row[2]</option>";
            //}
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
    $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

    $query = "select * from position_user_rpt WHERE position_level >= 4 and is_active = 1 order by position_level";
    $result = $db->prepare($query);
    $result->execute();

    $num = $result->rowCount();

    if ($num > 0) {
        $strCmbTTD4 = $strCmbTTD4 . "<option></option>";

        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            //if($row[0]==$cmbPengadaan2){
            //    $strCmbTTD4 = $strCmbTTD4."<option value='$row[0]' selected>$row[1] - $row[2]</option>";
            //}else{
            $strCmbTTD4 = $strCmbTTD4 . "<option value='$row[0]'>$row[1] - $row[2]</option>";
            //}
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
                <h2 class="with-border">Pertanggungjawaban Uang Muka : <?PHP echo $txtKodeUM; ?></h2>
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
                        <label class="col-sm-2 form-control-label">Lampiran UM</label>
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
                    </div>

                    <h5 class="with-border">Lampiran PJUM</h5>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Lampiran PJUM *</label>
                        <div class="col-sm-10">
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="file" class="form-control" name="txtLampiran2" id="txtLampiran2">
                                </div>
                            </div>
                        </div>
                        <label class="col-sm-2 form-control-label">Bukti Transfer Pengembalian Kelebihan UM</label>
                        <div class="col-sm-10">
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="file" class="form-control" name="txtKelebihan" id="txtKelebihan">
                                </div>
                            </div>
                        </div>
                        <label class="col-sm-2 form-control-label"></label>
                        <div class="col-sm-10">
                            <div class="form-group">
                                <div class="input-group">
                                    <?PHP if ($txtKelebihan == "") {
                                        echo "<span class='color-red'>Bukti Transfer Pengembalian Kelebihan UM belum diupload !!</span>";
                                    } else {
                                        echo "<a href='files/$txtKelebihan' target='_blank'>" . "Download Bukti Transfer Pengembalian Kelebihan UM</a>";
                                    } ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h5 class="with-border">User / Pemohon</h5>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Yang Meminta *</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><select class="select2" name="cmbUser1" id="cmbUser1"><?PHP echo $strCmbTTD1; ?>
                                </select></p>
                        </div>
                        <label class="col-sm-2 form-control-label">Disetujui *</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><select class="select2" name="cmbUser2" id="cmbUser2"><?PHP echo $strCmbTTD2; ?>
                                </select></p>
                        </div>
                    </div>

                    <!--<h5 class="with-border">Divisi Pengadaan</h5>
                        <div class="form-group row">
                            <label class="col-sm-2 form-control-label">Disiapkan *</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><select class="select2" name="cmbPengadaan1" id="cmbPengadaan1"><?PHP //echo $strCmbTTD3;
                                                                                                                                ?>
                                </select></p>
                            </div>
                            <label class="col-sm-2 form-control-label">Disetujui *</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><select class="select2" name="cmbPengadaan2" id="cmbPengadaan2"><?PHP //echo $strCmbTTD4;
                                                                                                                                ?>
                                </select></p>
                            </div>
                        </div>-->
            </div>
            <div class="box-typical box-typical-padding">
                <div class="form-group row">
                    <div class="col-sm-1">
                        <button type="submit" class="btn btn-inline" name="btnSimpan" id="btnSimpan">Simpan</button>
                    </div>
                    <div class="col-sm-1">
                        <button type="submit" class="btn btn-inline" name="btnCancel" id="btnCancel">Cancel</button>
                    </div>
                </div>
                </form>
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
            $('#txtTglPerolehan').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true
            });
        });
    </script>

    <script src="js/app.js"></script>
</body>

</html>