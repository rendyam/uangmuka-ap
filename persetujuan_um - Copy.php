<?PHP
//    session_start();
//
//    if (!isset($_SESSION['sessunameuangmuka'])) {
//        header('location:index.php');
//    }

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

if (isset($_POST['btnApprove']) and $_SERVER['REQUEST_METHOD'] == "POST") {
    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

        if ($txtAct == 1) {
            $query = "select user_penyetuju_id, user_penyetuju_email, tgl_um from t_um_rpt where t_um_id = '" . $txtKode . "' and tgl_um = '" . $txtParam . "'";

            $result = $db->prepare($query);
            $result->execute();

            $num = $result->rowCount();

            $count = 0;

            if ($num > 0) {
                while ($row = $result->fetch(PDO::FETCH_NUM)) {
                    $db->beginTransaction();

                    $txtStatus = "Approved";

                    $sqlQuery = $db->prepare("update t_um_status_tab set um_status = :um_status, tgl_status = now() where t_um_id = :t_um_id and um_position_user_id = :um_position_user_id");

                    $sqlQuery->bindParam(':um_status', $txtStatus, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':um_position_user_id', $txtUser, PDO::PARAM_STR);

                    $sqlQuery->execute();

                    $db->commit();

                    $db->beginTransaction();

                    $status_ref = 2;

                    $sqlQuery = $db->prepare("insert t_um_status_tab(t_um_id, um_position_user_id, status_ref) values(:t_um_id, :um_position_user_id, :status_ref)");

                    $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':um_position_user_id', $row[0], PDO::PARAM_STR);
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
                        $mail->Subject = "Persetujuan Permintaan Uang Muka";
                        $mail->Body    = "Untuk melakukan persetujuan permintaan uang muka klik alamat di bawah ini :<br><a href='$internalLink/uangmuka/persetujuan_um.php?id=" . base64_encode($txtKode) . "&param=" . base64_encode($row[2]) . "&u=" . base64_encode($row[0]) . "&act=2'>Link Internal Sistem Uang Muka</a>" . "<br><br><a href='$externalLink/uangmuka/persetujuan_um.php?id=" . base64_encode($txtKode) . "&param=" .  base64_encode($row[2]) . "&u=" .  base64_encode($row[0]) . "&act=2'>Link External Sistem Uang Muka</a>";
                        $mail->Send();

                        $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
                    } else {
                        $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
                    }

                    //header('location:permintaan_overview.php');
                }
            }
        } elseif ($txtAct == 2) {
            $query = "select pengadaan_disiapkan_id, pengadaan_disiapkan_email, tgl_um from t_um_rpt where t_um_id = '" . $txtKode . "' and tgl_um = '" . $txtParam . "'";

            $result = $db->prepare($query);
            $result->execute();

            $num = $result->rowCount();

            $count = 0;

            if ($num > 0) {
                while ($row = $result->fetch(PDO::FETCH_NUM)) {
                    $db->beginTransaction();

                    $txtStatus = "Approved";

                    $sqlQuery = $db->prepare("update t_um_status_tab set um_status = :um_status, tgl_status = now() where t_um_id = :t_um_id and um_position_user_id = :um_position_user_id");

                    $sqlQuery->bindParam(':um_status', $txtStatus, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':um_position_user_id', $txtUser, PDO::PARAM_STR);

                    $sqlQuery->execute();

                    $db->commit();

                    $db->beginTransaction();

                    $status_ref = 3;

                    $sqlQuery = $db->prepare("insert t_um_status_tab(t_um_id, um_position_user_id, status_ref) values(:t_um_id, :um_position_user_id, :status_ref)");

                    $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':um_position_user_id', $row[0], PDO::PARAM_STR);
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
                        $mail->Subject = "Persetujuan Permintaan Uang Muka";
                        $mail->Body    = "Untuk melakukan persetujuan permintaan uang muka klik alamat di bawah ini :<br><a href='$internalLink/uangmuka/persetujuan_um.php?id=" . base64_encode($txtKode) . "&param=" . base64_encode($row[2]) . "&u=" . base64_encode($row[0]) . "&act=3'>Link Internal Sistem Uang Muka</a>" . "<br><br><a href='$externalLink/uangmuka/persetujuan_um.php?id=" . base64_encode($txtKode) . "&param=" .  base64_encode($row[2]) . "&u=" .  base64_encode($row[0]) . "&act=3'>Link External Sistem Uang Muka</a>";
                        $mail->Send();

                        $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
                    } else {
                        $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
                    }

                    //header('location:permintaan_overview.php');
                }
            }
        } elseif ($txtAct == 3) {
            $query = "select pengadaan_disetujui_id, pengadaan_disetujui_email, tgl_um from t_um_rpt where t_um_id = '" . $txtKode . "' and tgl_um = '" . $txtParam . "'";

            $result = $db->prepare($query);
            $result->execute();

            $num = $result->rowCount();

            $count = 0;

            if ($num > 0) {
                while ($row = $result->fetch(PDO::FETCH_NUM)) {
                    $db->beginTransaction();

                    $txtStatus = "Approved";

                    $sqlQuery = $db->prepare("update t_um_status_tab set um_status = :um_status, tgl_status = now() where t_um_id = :t_um_id and um_position_user_id = :um_position_user_id");

                    $sqlQuery->bindParam(':um_status', $txtStatus, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':um_position_user_id', $txtUser, PDO::PARAM_STR);

                    $sqlQuery->execute();

                    $db->commit();

                    $db->beginTransaction();

                    $status_ref = 4;

                    $sqlQuery = $db->prepare("insert t_um_status_tab(t_um_id, um_position_user_id, status_ref) values(:t_um_id, :um_position_user_id, :status_ref)");

                    $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':um_position_user_id', $row[0], PDO::PARAM_STR);
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
                        $mail->Subject = "Persetujuan Permintaan Uang Muka";
                        $mail->Body    = "Untuk melakukan persetujuan permintaan uang muka klik alamat di bawah ini :<br><a href='$internalLink/uangmuka/persetujuan_um.php?id=" . base64_encode($txtKode) . "&param=" . base64_encode($row[2]) . "&u=" . base64_encode($row[0]) . "&act=4'>Link Internal Sistem Uang Muka</a>" . "<br><br><a href='$externalLink/uangmuka/persetujuan_um.php?id=" . base64_encode($txtKode) . "&param=" .  base64_encode($row[2]) . "&u=" .  base64_encode($row[0]) . "&act=4'>Link External Sistem Uang Muka</a>";
                        $mail->Send();

                        $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
                    } else {
                        $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
                    }
                }
            }
        } elseif ($txtAct == 4) {
            $query = "select id, email, created_at from position_user_rpt where um_eval = 1";

            $result = $db->prepare($query);
            $result->execute();

            $num = $result->rowCount();

            $count = 0;

            if ($num > 0) {
                while ($row = $result->fetch(PDO::FETCH_NUM)) {
                    $db->beginTransaction();

                    $txtStatus = "Approved";

                    $sqlQuery = $db->prepare("update t_um_status_tab set um_status = :um_status, tgl_status = now() where t_um_id = :t_um_id and um_position_user_id = :um_position_user_id");

                    $sqlQuery->bindParam(':um_status', $txtStatus, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':um_position_user_id', $txtUser, PDO::PARAM_STR);

                    $sqlQuery->execute();

                    $db->commit();

                    $db->beginTransaction();

                    $txtStatus = "Verification";

                    $sqlQuery = $db->prepare("update t_um_tab set status_um = :status_um where t_um_id = :t_um_id");

                    $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':status_um', $txtStatus, PDO::PARAM_STR);

                    $sqlQuery->execute();

                    $db->commit();

                    //                        $db->beginTransaction();
                    //                        
                    //                        $sqlQuery = $db->prepare("insert t_um_status_tab(t_um_id, um_position_user_id) values(:t_um_id, :um_position_user_id)");
                    //
                    //                        $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
                    //                        $sqlQuery->bindParam(':um_position_user_id', $row[0], PDO::PARAM_STR);
                    //
                    //                        $sqlQuery->execute();
                    //
                    //                        $db->commit();

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
                        $mail->Subject = "Persetujuan Permintaan Uang Muka";
                        $mail->Body    = "Untuk melakukan persetujuan permintaan uang muka klik alamat di bawah ini :<br><a href='$internalLink/uangmuka/evaluasi_um.php?id=" . base64_encode($txtKode) . "&param=" . base64_encode($row[2]) . "&u=" . base64_encode($row[0]) . "'>Link Internal Sistem Uang Muka</a>" . "<br><br><a href='$externalLink/uangmuka/evaluasi_um.php?id=" . base64_encode($txtKode) . "&param=" .  base64_encode($row[2]) . "&u=" .  base64_encode($row[0]) . "'>Link External Sistem Uang Muka</a>";
                        $mail->Send();

                        $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
                    } else {
                        $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
                    }

                    //header('location:permintaan_overview.php');
                }
            }
        } elseif ($txtAct == 5) {
            $query = "select keuangan_penyetuju_2_id, keuangan_penyetuju_2_email, tgl_um from t_um_rpt where t_um_id = '" . $txtKode . "' and tgl_um = '" . $txtParam . "' and keuangan_penyetuju_2 <> ''";

            $result = $db->prepare($query);
            $result->execute();

            $num = $result->rowCount();

            $count = 0;

            if ($num > 0) {
                while ($row = $result->fetch(PDO::FETCH_NUM)) {
                    $db->beginTransaction();

                    $txtStatus = "Approved";

                    $sqlQuery = $db->prepare("update t_um_status_tab set um_status = :um_status, tgl_status = now() where t_um_id = :t_um_id and um_position_user_id = :um_position_user_id");

                    $sqlQuery->bindParam(':um_status', $txtStatus, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':um_position_user_id', $txtUser, PDO::PARAM_STR);

                    $sqlQuery->execute();

                    $db->commit();

                    $db->beginTransaction();

                    $status_ref = 6;

                    $sqlQuery = $db->prepare("insert t_um_status_tab(t_um_id, um_position_user_id, status_ref) values(:t_um_id, :um_position_user_id, :status_ref)");

                    $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':um_position_user_id', $row[0], PDO::PARAM_STR);
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
                        $mail->Subject = "Persetujuan Permintaan Uang Muka";
                        $mail->Body    = "Untuk melakukan persetujuan permintaan uang muka klik alamat di bawah ini :<br><a href='$internalLink/uangmuka/persetujuan_um.php?id=" . base64_encode($txtKode) . "&param=" . base64_encode($row[2]) . "&u=" . base64_encode($row[0]) . "&act=6'>Link Internal Sistem Uang Muka</a>" . "<br><br><a href='$externalLink/uangmuka/persetujuan_um.php?id=" . base64_encode($txtKode) . "&param=" .  base64_encode($row[2]) . "&u=" .  base64_encode($row[0]) . "&act=6'>Link External Sistem Uang Muka</a>";
                        $mail->Send();

                        $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
                    } else {
                        $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
                    }
                }
            } else {
                $db->beginTransaction();

                $txtStatus = "Approved";

                $sqlQuery = $db->prepare("update t_um_status_tab set um_status = :um_status, tgl_status = now() where t_um_id = :t_um_id and um_position_user_id = :um_position_user_id");

                $sqlQuery->bindParam(':um_status', $txtStatus, PDO::PARAM_STR);
                $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
                $sqlQuery->bindParam(':um_position_user_id', $txtUser, PDO::PARAM_STR);

                $sqlQuery->execute();

                $db->commit();

                $db->beginTransaction();

                $sqlQuery = $db->prepare("update t_um_tab set status_um = :status_um where t_um_id = :t_um_id");

                $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
                $sqlQuery->bindParam(':status_um', $txtStatus, PDO::PARAM_STR);

                $sqlQuery->execute();

                $db->commit();

                if ($sqlQuery->rowCount() > 0) {
                    $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
                } else {
                    $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
                }
            }
        } elseif ($txtAct == 6) {
            $db->beginTransaction();

            $txtStatus = "Approved";

            $sqlQuery = $db->prepare("update t_um_status_tab set um_status = :um_status, tgl_status = now() where t_um_id = :t_um_id and um_position_user_id = :um_position_user_id");

            $sqlQuery->bindParam(':um_status', $txtStatus, PDO::PARAM_STR);
            $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
            $sqlQuery->bindParam(':um_position_user_id', $txtUser, PDO::PARAM_STR);

            $sqlQuery->execute();

            $db->commit();

            $db->beginTransaction();

            $sqlQuery = $db->prepare("update t_um_tab set status_um = :status_um where t_um_id = :t_um_id");

            $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
            $sqlQuery->bindParam(':status_um', $txtStatus, PDO::PARAM_STR);

            $sqlQuery->execute();

            $db->commit();

            if ($sqlQuery->rowCount() > 0) {
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
            $cmbKeuangan1 = $row[37] . " - " . $row[38] . " (" . $row[79] . " " . $row[80] . ")";
            $cmbKeuangan2 = $row[39] . " - " . $row[40] . " (" . $row[81] . " " . $row[82] . ")";
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

$strCmbTTD1 = "";

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

    $query = "select * from position_user_rpt WHERE position_level >= 4 order by position_level";
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

    $query = "select * from position_user_rpt WHERE position_level >= 4 order by position_level";
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

    $query = "select * from position_user_rpt WHERE position_level >= 4 order by position_level";
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

    $query = "select * from position_user_rpt WHERE position_level >= 4 order by position_level";
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

<body class="without-side-menu">

    <div class="page-content">
        <?PHP echo $strMessage; ?>
        <div class="container-fluid">
            <div class="box-typical box-typical-padding">
                <h2 class="with-border">Persetujuan Uang Muka : <?PHP echo $txtKode; ?></h2>
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
            </div>
            <div class="box-typical box-typical-padding">
                <div class="form-group row">
                    <div class="col-sm-1">
                        <button type="submit" class="btn btn-inline" name="btnApprove" id="btnApprove">Approve</button>
                    </div>
                    <div class="col-sm-1">
                        <button type="submit" class="btn btn-inline" name="btnReject" id="btnReject">Reject</button>
                    </div>
                    <?PHP
                    if ($txtAct == 5 || $txtAct == 6) {
                        echo "<div class='col-sm-1'>
                                            <a class='btn btn-success' href='evaluasi_rincian_um_app.php?id=" . $_GET['id'] . "&param=" . $_GET['param'] . "&u=" . $_GET['u'] . "&act=" . $_GET['act'] . "'>
                                                <i class='icon-edit icon-white'></i>  
                                                Evaluasi Rincian UM                                          
                                            </a>
                                        </div>";
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