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
$txtKeperluan = "";
$txtPeriode = "";
$txtLampiran = "";
$txtStatProposed = "";
$txtNilaiUM = "";
$txtEvaluasiNilaiUM = "";

// TAMBAH
if (isset($_POST['btnSimpan']) and $_SERVER['REQUEST_METHOD'] == "POST") {
    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

        $txtTglPerolehan = $_POST['txtTglPerolehan'];
        $txtTglPerolehan = explode('/', $txtTglPerolehan);
        $txtTglPerolehan = $txtTglPerolehan[2] . "-" . $txtTglPerolehan[0] . "-" . $txtTglPerolehan[1];

        $txtNote = $_POST['txtNote'];
        $txtTandaTerima = $_FILES['txtTandaTerima']['name'];

        if ($txtTglPerolehan == "") {
            $strMessage = "<div class='alert alert-error'><strong>Kolom dengan tanda bintang (*) wajib diisi</strong></div>";
        } else {
            if ($txtTandaTerima == '') {
                $db->beginTransaction();

                $txtStatus = "Received by User";
                $id_update_received = $_SESSION['sessiduangmuka'];

                $sqlQuery = $db->prepare("
                                        update 
                                            t_um_tab 
                                        set 
                                            tgl_diterima = :tgl_diterima, 
                                            status_um = :status_um, 
                                            note_diterima = :note_diterima,
                                            update_received = now(),
                                            id_update_received = :id_update_received
                                        where 
                                            t_um_id = :t_um_id");

                $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
                $sqlQuery->bindParam(':tgl_diterima', $txtTglPerolehan, PDO::PARAM_STR);
                $sqlQuery->bindParam(':status_um', $txtStatus, PDO::PARAM_STR);
                $sqlQuery->bindParam(':note_diterima', $txtNote, PDO::PARAM_STR);
                $sqlQuery->bindParam(':id_update_received', $id_update_received, PDO::PARAM_STR);
                $sqlQuery->execute();

                $db->commit();

                if ($sqlQuery->rowCount() > 0) {
                    //header('location:permintaan_edit.php?id='.base64_encode($txtKode));

                    //header('location:home.php');
                    $query = "SELECT a.user_peminta_email FROM t_um_rpt a WHERE a.t_um_id = '$txtKode'";
                    $result = $db->prepare($query);
                    $result->execute();

                    $num = $result->rowCount();

                    if ($num > 0) {
                        while ($row = $result->fetch(PDO::FETCH_NUM)) {
                            $txtEmail = $row[0];

                            $mail = new PHPMailer();
                            $mail->IsSMTP();
                            $mail->Host = "mail.ptkbs.co.id";
                            $mail->SMTPAuth = true;
                            $mail->SMTPSecure = "tls";
                            $mail->Port     = 587;
                            $mail->Username = "developer@ptkbs.co.id";
                            $mail->Password = "Cigading123";
                            $mail->AddAddress($txtEmail, $txtEmail);
                            $mail->From = "developer@ptkbs.co.id";
                            $mail->FromName = "Sistem Uang Muka";
                            $mail->IsHTML(true);
                            $mail->Subject = "Sudah Diambil Uang Muka No : " . $txtKode;
                            $mail->Body    = "Sudah Diambil uang muka anda nomor : " . $txtKode;
                            $mail->Send();
                        }
                    }

                    $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
                } else {
                    $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
                }
            } else {
                $path = "files/";

                $name = $_FILES['txtTandaTerima']['name'];
                $size = $_FILES['txtTandaTerima']['size'];

                if (strlen($name)) {
                    //if($size > 250000){
                    //    $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Ukuran KTP terlalu besar !!</div>";
                    //}else{
                    //list($txt, $ext) = explode(".", $name);

                    //$actual_image_name = "KTP_".$kdPelamar.".".strtolower($ext);

                    $actual_image_name = "TT_" . $txtKode . "_" . $name;
                    $tmp = $_FILES['txtTandaTerima']['tmp_name'];

                    if (move_uploaded_file($tmp, $path . $actual_image_name)) {
                        $db->beginTransaction();

                        $txtStatus = "Received by User";
                        $id_update_received = $_SESSION['sessiduangmuka'];

                        $sqlQuery = $db->prepare("
                                                update 
                                                    t_um_tab 
                                                set 
                                                    tgl_diterima = :tgl_diterima, 
                                                    status_um = :status_um, 
                                                    note_diterima = :note_diterima, 
                                                    tanda_terima = :tanda_terima,
                                                    update_received = now(),
                                                    id_update_received = :id_update_received
                                                where 
                                                    t_um_id = :t_um_id");

                        $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
                        $sqlQuery->bindParam(':tgl_diterima', $txtTglPerolehan, PDO::PARAM_STR);
                        $sqlQuery->bindParam(':status_um', $txtStatus, PDO::PARAM_STR);
                        $sqlQuery->bindParam(':note_diterima', $txtNote, PDO::PARAM_STR);
                        $sqlQuery->bindParam(':tanda_terima', $actual_image_name, PDO::PARAM_STR);
                        $sqlQuery->bindParam(':id_update_received', $id_update_received, PDO::PARAM_STR);
                        $sqlQuery->execute();

                        $db->commit();

                        if ($sqlQuery->rowCount() > 0) {
                            //header('location:permintaan_edit.php?id='.base64_encode($txtKode));

                            //header('location:home.php');
                            $query = "SELECT a.user_peminta_email FROM t_um_rpt a WHERE a.t_um_id = '$txtKode'";
                            $result = $db->prepare($query);
                            $result->execute();

                            $num = $result->rowCount();

                            if ($num > 0) {
                                while ($row = $result->fetch(PDO::FETCH_NUM)) {
                                    $txtEmail = $row[0];

                                    $mail = new PHPMailer();
                                    $mail->IsSMTP();
                                    $mail->Host = "mail.ptkbs.co.id";
                                    $mail->SMTPAuth = true;
                                    $mail->SMTPSecure = "tls";
                                    $mail->Port     = 587;
                                    $mail->Username = "developer@ptkbs.co.id";
                                    $mail->Password = "Cigading123";
                                    $mail->AddAddress($txtEmail, $txtEmail);
                                    $mail->From = "developer@ptkbs.co.id";
                                    $mail->FromName = "Sistem Uang Muka";
                                    $mail->IsHTML(true);
                                    $mail->Subject = "Sudah Diambil Uang Muka No : " . $txtKode;
                                    $mail->Body    = "Sudah Diambil uang muka anda nomor : " . $txtKode;
                                    $mail->Send();
                                }
                            }

                            $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
                        } else {
                            $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
                        }
                    }
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
                        $mail->Body    = "Untuk melakukan persetujuan permintaan uang muka klik alamat di bawah ini :<br><a href='$internalLink/uangmuka/persetujuan_um.php?id=" . base64_encode($txtKode) . "&param=" . base64_encode($row[2]) . "&u=" . base64_encode($row[0]) . "&act=5'>Link Internal Sistem Uang Muka</a>" . "<br><br><a href='$externalLink/uangmuka/persetujuan_um.php?id=" . base64_encode($txtKode) . "&param=" .  base64_encode($row[2]) . "&u=" .  base64_encode($row[0]) . "&act=5'>Link External Sistem Uang Muka</a>";
                        $mail->Send();
                    }

                    header('location:evaluasi_overview.php');
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
            $cmbKeuangan1 = $row[41] . " - " . $row[42];
            $cmbKeuangan2 = $row[37] . " - " . $row[38] . " (" . $row[79] . " " . $row[80] . ")";
            $cmbKeuangan3 = $row[39] . " - " . $row[40] . " (" . $row[81] . " " . $row[82] . ")";
            $notes = $row[83];
            $txtTT = $row[84];
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
    $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

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
                <h2 class="with-border">Permintaan Uang Muka : <?PHP echo $txtKode; ?></h2>
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
                            <p class="form-control-static"><PHP echo $cmbPengadaan1; ?></p>
                        </div>
                        <label class="col-sm-2 form-control-label">Disetujui *</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><PHP echo $cmbPengadaan2; ?></p>
                        </div>
                    </div>
					-->
                    <h5 class="with-border">Keuangan</h5>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Diperiksa *</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><?PHP echo $cmbKeuangan1; ?></p>
                        </div>
                        <label class="col-sm-2 form-control-label">Disetujui 1 *</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><?PHP echo $cmbKeuangan2; ?></p>
                        </div>
                        <label class="col-sm-2 form-control-label">Disetujui 2</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><?PHP echo $cmbKeuangan3; ?></p>
                        </div>
                    </div>

                    <h5 class="with-border">Diterima User</h5>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Tgl Diterima User *</label>
                        <div class="col-sm-10">
                            <div class="form-group">
                                <div class="input-group date">
                                    <input name="txtTglPerolehan" id="txtTglPerolehan" type="text" class="form-control">
                                    <div class="input-group-addon">
                                        <span class="font-icon font-icon-calend"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <label class="col-sm-2 form-control-label">Note</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><textarea rows="3" class="form-control" name="txtNote" id="txtNote"><?PHP echo $notes; ?></textarea></p>
                        </div>
                        <!-- <php print_r($_SESSION); > -->
                        <label class="col-sm-2 form-control-label">Tanda Terima / Bukti Transfer</label>
                        <div class="col-sm-10">
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="file" class="form-control" name="txtTandaTerima" id="txtTandaTerima">
                                </div>
                            </div>
                        </div>
                        <label class="col-sm-2 form-control-label"></label>
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
            </div>
            <div class="box-typical box-typical-padding">
                <div class="form-group row">
                    <div class="col-sm-1">
                        <button type="submit" class="btn btn-inline" name="btnSimpan" id="btnSimpan">Simpan</button>
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