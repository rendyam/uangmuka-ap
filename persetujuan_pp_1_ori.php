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
$txtParam = base64_decode($_GET['param']);
$txtUser = base64_decode($_GET['u']);
$txtAct = $_GET['act'];
$txtNilaiUM = "";
$txtEvaluasiNilaiUM = "";
$aging = 0;
$uangmukaID = "";
$estimasi = "";
$alasan = "";

if (isset($_POST['btnApprove']) and $_SERVER['REQUEST_METHOD'] == "POST") {
    try {
        $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

        if ($txtAct == 1) {
            $query = "select user_penyetuju_id, user_penyetuju_email, tgl_um from t_um_rpt where t_um_id = (select t_um_id from t_dispensasi_tab where t_dispensasi_id = '" . $txtKode . "') and tgl_um = '" . $txtParam . "'";

            $result = $db->prepare($query);
            $result->execute();

            $num = $result->rowCount();

            $count = 0;

            if ($num > 0) {
                while ($row = $result->fetch(PDO::FETCH_NUM)) {
                    $db->beginTransaction();

                    $txtStatus = "Approved";

                    $sqlQuery = $db->prepare("update t_dispensasi_status_tab set dispensasi_status = :dispensasi_status, tgl_status = now() where t_dispensasi_id = :t_dispensasi_id and dispensasi_position_user_id = :dispensasi_position_user_id");

                    $sqlQuery->bindParam(':dispensasi_status', $txtStatus, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':t_dispensasi_id', $txtKode, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':dispensasi_position_user_id', $txtUser, PDO::PARAM_STR);

                    $sqlQuery->execute();

                    $db->commit();

                    $db->beginTransaction();

                    $status_ref = 2;

                    $sqlQuery = $db->prepare("delete from t_dispensasi_status_tab where t_dispensasi_id=:t_dispensasi_id and dispensasi_position_user_id=:dispensasi_position_user_id and status_ref=:status_ref");

                    $sqlQuery->bindParam(':t_dispensasi_id', $txtKode, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':dispensasi_position_user_id', $row[0], PDO::PARAM_STR);
                    $sqlQuery->bindParam(':status_ref', $status_ref, PDO::PARAM_STR);

                    $sqlQuery->execute();

                    $db->commit();

                    $db->beginTransaction();

                    $sqlQuery = $db->prepare("insert t_dispensasi_status_tab(t_dispensasi_id, dispensasi_position_user_id, status_ref) values(:t_dispensasi_id, :dispensasi_position_user_id, :status_ref)");

                    $sqlQuery->bindParam(':t_dispensasi_id', $txtKode, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':dispensasi_position_user_id', $row[0], PDO::PARAM_STR);
                    $sqlQuery->bindParam(':status_ref', $status_ref, PDO::PARAM_STR);

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
                        $mail->Subject = "Persetujuan Perpanjangan Pertanggungjawaban No : " . $txtKode;
                        $mail->Body    = "Untuk melakukan persetujuan perpanjangan pertanggungjawaban uang muka klik alamat di bawah ini :<br><a href='$internalLink/uangmuka/persetujuan_pp.php?id=" . base64_encode($txtKode) . "&param=" . base64_encode($row[2]) . "&u=" . base64_encode($row[0]) . "&act=2'>Link Internal Sistem Uang Muka</a>" . "<br><br><a href='$externalLink/uangmuka/persetujuan_pp.php?id=" . base64_encode($txtKode) . "&param=" .  base64_encode($row[2]) . "&u=" .  base64_encode($row[0]) . "&act=2'>Link External Sistem Uang Muka</a>";
                        $mail->Send();

                        //$strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";

                        header('location:persetujuan_overview.php');
                    } else {
                        $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
                    }

                    //header('location:permintaan_overview.php');
                }
            }
        } elseif ($txtAct == 2) {
            $query = "select keuangan_penyetuju_1_id, keuangan_penyetuju_1_email, tgl_um from t_um_rpt where t_um_id = (select t_um_id from t_dispensasi_tab where t_dispensasi_id = '" . $txtKode . "') and tgl_um = '" . $txtParam . "'";

            $result = $db->prepare($query);
            $result->execute();

            $num = $result->rowCount();

            $count = 0;

            if ($num > 0) {
                while ($row = $result->fetch(PDO::FETCH_NUM)) {
                    $db->beginTransaction();

                    $txtStatus = "Approved";

                    $sqlQuery = $db->prepare("update t_dispensasi_status_tab set dispensasi_status = :dispensasi_status, tgl_status = now() where t_dispensasi_id = :t_dispensasi_id and dispensasi_position_user_id = :dispensasi_position_user_id");

                    $sqlQuery->bindParam(':dispensasi_status', $txtStatus, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':t_dispensasi_id', $txtKode, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':dispensasi_position_user_id', $txtUser, PDO::PARAM_STR);

                    $sqlQuery->execute();

                    $db->commit();

                    $db->beginTransaction();

                    $status_ref = 3;

                    $sqlQuery = $db->prepare("delete from t_dispensasi_status_tab where t_dispensasi_id=:t_dispensasi_id and dispensasi_position_user_id=:dispensasi_position_user_id and status_ref=:status_ref");

                    $sqlQuery->bindParam(':t_dispensasi_id', $txtKode, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':dispensasi_position_user_id', $row[0], PDO::PARAM_STR);
                    $sqlQuery->bindParam(':status_ref', $status_ref, PDO::PARAM_STR);

                    $sqlQuery->execute();

                    $db->commit();

                    $db->beginTransaction();

                    $sqlQuery = $db->prepare("insert t_dispensasi_status_tab(t_dispensasi_id, dispensasi_position_user_id, status_ref) values(:t_dispensasi_id, :dispensasi_position_user_id, :status_ref)");

                    $sqlQuery->bindParam(':t_dispensasi_id', $txtKode, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':dispensasi_position_user_id', $row[0], PDO::PARAM_STR);
                    $sqlQuery->bindParam(':status_ref', $status_ref, PDO::PARAM_STR);

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
                        $mail->Subject = "Persetujuan Permintaan Perpanjangan Pertanggungjawaban No : " . $txtKode;
                        $mail->Body    = "Untuk melakukan persetujuan permintaan perpanjangan pertanggungjawaban klik alamat di bawah ini :<br><a href='$internalLink/uangmuka/persetujuan_pp.php?id=" . base64_encode($txtKode) . "&param=" . base64_encode($row[2]) . "&u=" . base64_encode($row[0]) . "&act=3'>Link Internal Sistem Uang Muka</a>" . "<br><br><a href='$externalLink/uangmuka/persetujuan_pp.php?id=" . base64_encode($txtKode) . "&param=" .  base64_encode($row[2]) . "&u=" .  base64_encode($row[0]) . "&act=3'>Link External Sistem Uang Muka</a>";
                        $mail->Send();

                        $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
                    } else {
                        $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
                    }

                    //header('location:permintaan_overview.php');
                }
            }
        } elseif ($txtAct == 3) {
            $db->beginTransaction();

            $txtStatus = "Approved";

            $sqlQuery = $db->prepare("update t_dispensasi_status_tab set dispensasi_status = :dispensasi_status, tgl_status = now() where t_dispensasi_id = :t_dispensasi_id and dispensasi_position_user_id = :dispensasi_position_user_id");

            $sqlQuery->bindParam(':dispensasi_status', $txtStatus, PDO::PARAM_STR);
            $sqlQuery->bindParam(':t_dispensasi_id', $txtKode, PDO::PARAM_STR);
            $sqlQuery->bindParam(':dispensasi_position_user_id', $txtUser, PDO::PARAM_STR);

            $sqlQuery->execute();

            $db->commit();

            $db->beginTransaction();

            $sqlQuery = $db->prepare("update t_dispensasi_tab set status_perpanjangan = :status_perpanjangan where t_dispensasi_id = :t_dispensasi_id");

            $sqlQuery->bindParam(':t_dispensasi_id', $txtKode, PDO::PARAM_STR);
            $sqlQuery->bindParam(':status_perpanjangan', $txtStatus, PDO::PARAM_STR);

            $sqlQuery->execute();

            $db->commit();

            if ($sqlQuery->rowCount() > 0) {
                $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
            } else {
                $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
            }

            //                $query = "select pengadaan_disetujui_id, pengadaan_disetujui_email, tgl_um from t_um_rpt where t_um_id = '".$txtKode."' and tgl_um = '".$txtParam."'";
            //
            //                $result = $db->prepare($query);
            //                $result->execute();     
            //
            //                $num = $result->rowCount();
            //
            //                $count = 0;
            //
            //                if($num > 0) {
            //                    while ($row = $result->fetch(PDO::FETCH_NUM)) {
            //                        $db->beginTransaction();
            //
            //                        $txtStatus = "Approved";
            //
            //                        $sqlQuery = $db->prepare("update t_um_status_tab set um_status = :um_status, tgl_status = now() where t_um_id = :t_um_id and um_position_user_id = :um_position_user_id");
            //
            //                        $sqlQuery->bindParam(':um_status', $txtStatus, PDO::PARAM_STR);
            //                        $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
            //                        $sqlQuery->bindParam(':um_position_user_id', $txtUser, PDO::PARAM_STR);
            //
            //                        $sqlQuery->execute();
            //
            //                        $db->commit();
            //                        
            //                        $db->beginTransaction();
            //                        
            //                        $status_ref = 4;
            //                        
            //                        $sqlQuery = $db->prepare("delete from t_um_status_tab where t_um_id=:t_um_id and um_position_user_id=:um_position_user_id and status_ref=:status_ref");
            //
            //                        $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
            //                        $sqlQuery->bindParam(':um_position_user_id', $row[0], PDO::PARAM_STR);
            //                        $sqlQuery->bindParam(':status_ref', $status_ref, PDO::PARAM_STR);
            //
            //                        $sqlQuery->execute();
            //
            //                        $db->commit();
            //
            //                        $db->beginTransaction();
            //                    
            //                        $sqlQuery = $db->prepare("insert t_um_status_tab(t_um_id, um_position_user_id, status_ref) values(:t_um_id, :um_position_user_id, :status_ref)");
            //
            //                        $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
            //                        $sqlQuery->bindParam(':um_position_user_id', $row[0], PDO::PARAM_STR);
            //                        $sqlQuery->bindParam(':status_ref', $status_ref, PDO::PARAM_STR);
            //
            //                        $sqlQuery->execute();
            //
            //                        $db->commit();
            //
            //                        if($sqlQuery->rowCount() > 0){
            //                            $mail = new PHPMailer();
            //                            $mail->IsSMTP();              
            //                            $mail->Host = "mail.ptkbs.co.id";  
            //                            $mail->SMTPAuth = true;     
            //                            $mail->SMTPSecure = "tls";
            //                            $mail->Port     = 587; 
            //                            $mail->Username = "developer@ptkbs.co.id";  
            //                            $mail->Password = "Cigading123"; 
            //                            $mail->AddAddress($row[1], $row[1]);
            //                            $mail->From = "developer@ptkbs.co.id";
            //                            $mail->FromName = "Sistem Uang Muka";
            //                            $mail->IsHTML(true);
            //                            $mail->Subject = "Persetujuan Permintaan Uang Muka No : ".$txtKode;
            //                            $mail->Body    = "Untuk melakukan persetujuan permintaan uang muka klik alamat di bawah ini :<br><a href='$internalLink/uangmuka/persetujuan_um.php?id=".base64_encode($txtKode)."&param=".base64_encode($row[2])."&u=".base64_encode($row[0])."&act=4'>Link Internal Sistem Uang Muka</a>"."<br><br><a href='$externalLink/uangmuka/persetujuan_um.php?id=".base64_encode($txtKode)."&param=".  base64_encode($row[2])."&u=".  base64_encode($row[0])."&act=4'>Link External Sistem Uang Muka</a>";
            //                            $mail->Send();
            //                            
            //                            $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
            //                        }else{
            //                            $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
            //                        }
            //                    }
            //                }
        }
    } catch (PDOException $e) {
        if ($modeDebug == 0) {
            $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Oops, there is something wrong.....</div>";
        } else {
            $strMessage = $e->getMessage();
        }
    }
}

if (isset($_POST['btnReject']) and $_SERVER['REQUEST_METHOD'] == "POST") {
    try {
        $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

        $txtAlasan = $_POST['txtAlasan'];

        if ($txtAlasan == "") {
            $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'><strong>Untuk melakukan Reject, Alasan Reject wajib diisi !!</strong></div>";
        } else {
            $db->beginTransaction();

            $txtStatus = "Rejected";

            $sqlQuery = $db->prepare("update t_dispensasi_status_tab set dispensasi_status = :dispensasi_status, tgl_status = now() where t_dispensasi_id = :t_dispensasi_id and dispensasi_position_user_id = :dispensasi_position_user_id");

            $sqlQuery->bindParam(':dispensasi_status', $txtStatus, PDO::PARAM_STR);
            $sqlQuery->bindParam(':t_dispensasi_id', $txtKode, PDO::PARAM_STR);
            $sqlQuery->bindParam(':dispensasi_position_user_id', $txtUser, PDO::PARAM_STR);

            $sqlQuery->execute();

            $db->commit();

            $db->beginTransaction();

            $sqlQuery = $db->prepare("update t_dispensasi_tab set status_perpanjangan = :status_perpanjangan, note_reject = :note_reject where t_dispensasi_id = :t_dispensasi_id");

            $sqlQuery->bindParam(':t_dispensasi_id', $txtKode, PDO::PARAM_STR);
            $sqlQuery->bindParam(':status_perpanjangan', $txtStatus, PDO::PARAM_STR);
            $sqlQuery->bindParam(':note_reject', $txtAlasan, PDO::PARAM_STR);

            $sqlQuery->execute();

            $db->commit();

            if ($sqlQuery->rowCount() > 0) {
                $query = "SELECT a.user_peminta_email FROM t_um_rpt a WHERE a.t_um_id = (select t_um_id from t_dispensasi_tab where t_dispensasi_id = '$txtKode')";
                $result = $db->prepare($query);
                $result->execute();

                $num = $result->rowCount();

                if ($num > 0) {
                    while ($row = $result->fetch(PDO::FETCH_NUM)) {
                        $txtEmail = $row[0];
                    }
                }

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
                $mail->Subject = "Permintaan Perpanjangan Pertanggungjawaban No : " . $txtKode . " Ditolak";
                $mail->Body    = "Permintaan perpanjangan pertanggungjawaban anda nomor : " . $txtKode . " ditolak.";
                $mail->Send();

                //$strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
                header('location:persetujuan_overview.php');
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

    $query = "select * from t_um_rpt where t_um_id= (select t_um_id from t_dispensasi_tab where t_dispensasi_id = '$txtKode')";
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

            //                $approvalStatus = "";
            //                
            //                if($txtAct==1){
            //                    $approvalStatus = $row[71];
            //                }elseif($txtAct==2){
            //                    $approvalStatus = $row[73];
            //                }elseif($txtAct==3){
            //                    $approvalStatus = $row[75];
            //                }elseif($txtAct==4){
            //                    $approvalStatus = $row[77];
            //                }elseif($txtAct==5){
            //                    $approvalStatus = $row[79];
            //                }elseif($txtAct==6){
            //                    $approvalStatus = $row[81];
            //                }

            $txtAlasan = $row[96];
            $aging = $row[70];
            $uangmukaID = $row[0];
            //$user1Status = $row[71];
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

try {
    $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

    $query = "select * from t_dispensasi_tab where t_dispensasi_id = '$txtKode'";
    $result = $db->prepare($query);
    $result->execute();

    $num = $result->rowCount();

    if ($num > 0) {
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $estimasi = $row[3];
            $alasan = $row[4];
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

try {
    $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

    $query = "select * from t_dispensasi_status_tab where t_dispensasi_id = '$txtKode' and status_ref = $txtAct";
    $result = $db->prepare($query);
    $result->execute();

    $num = $result->rowCount();

    if ($num > 0) {
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $approvalStatus = "";

            $approvalStatus = $row[3];

            //                if($txtAct==1){
            //                    $approvalStatus = $row[71];
            //                }elseif($txtAct==2){
            //                    $approvalStatus = $row[73];
            //                }elseif($txtAct==3){
            //                    $approvalStatus = $row[75];
            //                }elseif($txtAct==4){
            //                    $approvalStatus = $row[77];
            //                }elseif($txtAct==5){
            //                    $approvalStatus = $row[79];
            //                }elseif($txtAct==6){
            //                    $approvalStatus = $row[81];
            //                }
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

    $query = "select * from t_um_detail_tab where t_um_id= '$uangmukaID' order by t_um_detail_id";
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
                <h2 class="with-border">Persetujuan Perpanjangan No : <?PHP echo $txtKode; ?></h2>
                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Estimasi Tgl Pertanggungjawaban</label>
                    <div class="col-sm-10">
                        <p class="form-control-static"><?PHP echo $estimasi; ?></p>
                    </div>
                    <label class="col-sm-2 form-control-label">Alasan</label>
                    <div class="col-sm-10">
                        <p class="form-control-static"><?PHP echo $alasan; ?></p>
                    </div>
                </div>
                <h2 class="with-border">Uang Muka No : <?PHP echo $uangmukaID . " (Aging : " . $aging . " hari)"; ?></h2>
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

                    <h5 class="with-border">Keuangan</h5>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Disiapkan *</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><?PHP echo $cmbKeuangan1; ?></p>
                        </div>
                        <label class="col-sm-2 form-control-label">Disetujui *</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><?PHP echo $cmbKeuangan2; ?></p>
                        </div>
                    </div>

                    <h5 class="with-border"><span class="color-red">Alasan Reject (Hanya Diisi Apabila Memilih Tombol Reject)</span></h5>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label"><span class="color-red">Alasan Reject</span></label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><textarea rows="3" class="form-control" name="txtAlasan" id="txtAlasan"><?PHP echo $txtAlasan; ?></textarea></p>
                        </div>
                    </div>
            </div>
            <div class="box-typical box-typical-padding">
                <div class="form-group row">
                    <?PHP
                    if ($approvalStatus == "") {
                        echo "<div class='col-sm-1'>
                                            <button type='submit' class='btn btn-inline' name='btnApprove' id='btnApprove'>Approve</button>
                                            </div>
                                            <div class='col-sm-1'>
                                                <button type='submit' class='btn btn-inline' name='btnReject' id='btnReject'>Reject</button>
                                            </div>";
                    } else {
                        echo "<div class='col-sm-12 alert alert-success'><strong>Anda sudah melakukan " . $approvalStatus . " untuk permintaan uang muka ini</strong></div>";
                    }
                    ?>
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