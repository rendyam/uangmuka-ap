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

// TAMBAH
if (isset($_POST['btnSimpan']) and $_SERVER['REQUEST_METHOD'] == "POST") {
    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

        $txtTglPerolehan = $_POST['txtTglPerolehan'];
        $txtTglPerolehan = explode('/', $txtTglPerolehan);
        $txtTglPerolehan = $txtTglPerolehan[2] . "-" . $txtTglPerolehan[0] . "-" . $txtTglPerolehan[1];

        $txtNama = $_POST['txtNama'];
        $cmbKategori = $_POST['cmbKategori'];
        $txtUmur = $_POST['txtUmur'];
        $txtLampiran = $_POST['txtLampiran'];
        $txtCatatan = $_POST['txtCatatan'];

        if ($txtKode == "" || $txtNama == "" || $cmbKategori == "" || $txtUmur == "" || $txtTglPerolehan == "") {
            $strMessage = "<div class='alert alert-error'><strong>Kolom dengan tanda bintang (*) wajib diisi</strong></div>";
        } else {
            $db->beginTransaction();

            $sqlQuery = $db->prepare("update fixed_asset_tab set nama_aset = :nama_aset, id_kategori = :id_kategori, tgl_perolehan = :tgl_perolehan, umur_ekonomis = :umur_ekonomis, catatan = :catatan where kode_aset = :kode_aset");

            $sqlQuery->bindParam(':kode_aset', $txtKode, PDO::PARAM_STR);
            $sqlQuery->bindParam(':nama_aset', $txtNama, PDO::PARAM_STR);
            $sqlQuery->bindParam(':id_kategori', $cmbKategori, PDO::PARAM_STR);
            $sqlQuery->bindParam(':tgl_perolehan', $txtTglPerolehan, PDO::PARAM_STR);
            $sqlQuery->bindParam(':umur_ekonomis', $txtUmur, PDO::PARAM_STR);
            $sqlQuery->bindParam(':catatan', $txtCatatan, PDO::PARAM_STR);

            $sqlQuery->execute();

            $db->commit();

            if ($sqlQuery->rowCount() > 0) {
                $path = "files/";

                $name = $_FILES['txtLampiran']['name'];
                $size = $_FILES['txtLampiran']['size'];

                if (strlen($name)) {
                    //if($size > 250000){
                    //    $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Ukuran KTP terlalu besar !!</div>";
                    //}else{
                    //list($txt, $ext) = explode(".", $name);

                    //$actual_image_name = "KTP_".$kdPelamar.".".strtolower($ext);
                    $actual_image_name = $txtKode . "_" . $name;
                    $tmp = $_FILES['txtLampiran']['tmp_name'];

                    if (move_uploaded_file($tmp, $path . $actual_image_name)) {
                        $db->beginTransaction();

                        $sqlQuery = $db->prepare("update fixed_asset_tab set lampiran = :lampiran where kode_aset = :kode_aset");

                        $sqlQuery->bindParam(':kode_aset', $txtKode, PDO::PARAM_STR);
                        $sqlQuery->bindParam(':lampiran', $actual_image_name, PDO::PARAM_STR);

                        $sqlQuery->execute();

                        $db->commit();

                        if ($sqlQuery->rowCount() > 0) {
                            //header('location:edit_aset.php?edit=true&id='.base64_encode($txtKode));
                            $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
                        } else {
                            $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
                        }
                    } else {
                        $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Upload lampiran gagal. Ulangi proses upload lampiran !!</div>";
                    }
                    //}
                }
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
            $cmbUser1 = $row[7];
            $cmbUser2 = $row[8];
            $cmbPengadaan1 = $row[9];
            $cmbPengadaan2 = $row[10];
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
    $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

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
    $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

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
    $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

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
                            <p class="form-control-static"><textarea rows="3" class="form-control" name="txtKeperluan" id="txtKeperluan"><?PHP echo $txtKeperluan; ?></textarea></p>
                        </div>
                        <label class="col-sm-2 form-control-label">Periode *</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><input type="text" class="form-control" name="txtPeriode" id="txtPeriode" value="<?PHP echo $txtPeriode; ?>"></p>
                        </div>
                        <label class="col-sm-2 form-control-label">Lampiran</label>
                        <div class="col-sm-10">
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="file" class="form-control" name="txtLampiran" id="txtLampiran">
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="box-typical box-typical-padding">
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
            </div>
            <div class="box-typical box-typical-padding">
				<!-- 
                <h5 class="with-border">Divisi Pengadaan</h5>
                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Disiapkan *</label>
                    <div class="col-sm-10">
                        <p class="form-control-static"><select class="select2" name="cmbPengadaan1" id="cmbPengadaan1"><PHP echo $strCmbTTD3; ?>
                            </select></p>
                    </div>
                    <label class="col-sm-2 form-control-label">Disetujui *</label>
                    <div class="col-sm-10">
                        <p class="form-control-static"><select class="select2" name="cmbPengadaan2" id="cmbPengadaan2"><PHP echo $strCmbTTD4; ?>
                            </select></p>
                    </div>
                </div>
				-->
            </div>
            <div class="box-typical box-typical-padding">
                <div class="form-group row">
                    <label class="col-sm-12 form-control-label">
                        <div class="alert alert-blue-dirty">Sesuai Prosedur Pengajuan dan Pertanggungjawaban Uang Muka, kami selaku User/Pemohon Uang Muka dengan ini menyatakan bahwa jika dalam jangka waktu 30 hari setelah tanggal penerimaan uang muka kami belum mempertanggungjawabkan uang muka tersebut, kami bersedia dikenakan sanksi administrasi berupa:<br>1. Pemblokiran Sistem Uang Muka Online sampai uang muka tersebut dipertanggungjawabkan atau dilakukan perpanjangan pertanggungjawaban Uang Muka dengan jangka waktu perpanjangan maksimal 14 hari kalender.<br>2. Penahanan tunjangan jika yang bersangkutan tidak dapat memenuhi kewajiban Perpanjangan Pertanggungjawaban Uang Muka sampai dengan Uang Muka tersebut dipertanggungjawabkan.<br></div>
                    </label>
                    <div class="col-sm-2">
                        <button type="submit" class="btn btn-inline" name="btnSimpan" id="btnSimpan">Simpan</button>
                    </div>
                    <div class="col-sm-2">
                        <button type="submit" class="btn btn-inline" name="btnPropose" id="btnPropose">Propose</button>
                    </div>
                    <div class="col-sm-2">
                        <button type="submit" class="btn btn-inline" name="btnCancel" id="btnCancel">Cancel</button>
                    </div>
                    <div class="col-sm-2">
                        <a class="btn btn-success" href="rincian_um.php?id=<?PHP echo $_GET['id']; ?>">
                            <i class="icon-edit icon-white"></i>
                            Input Rincian UM
                        </a>
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