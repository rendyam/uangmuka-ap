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
$txtNilaiPJUM = "";
$txtSisa = "";

if (isset($_POST['btnApprove']) and $_SERVER['REQUEST_METHOD'] == "POST") {
    try {
        $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

        if ($txtAct == 1) {
            $query = "select pj_user_penyetuju_id, pj_user_penyetuju_email, tgl_pjum from t_um_rpt where t_pjum_id = '" . $txtKode . "' and tgl_pjum = '" . $txtParam . "'";

            $result = $db->prepare($query);
            $result->execute();

            $num = $result->rowCount();

            $count = 0;

            if ($num > 0) {
                while ($row = $result->fetch(PDO::FETCH_NUM)) {
                    $db->beginTransaction();

                    $txtStatus = "Approved";

                    $sqlQuery = $db->prepare("update t_pjum_status_tab set pjum_status = :pjum_status, tgl_status = now() where t_pjum_id = :t_pjum_id and pjum_position_user_id = :pjum_position_user_id");

                    $sqlQuery->bindParam(':pjum_status', $txtStatus, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':t_pjum_id', $txtKode, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':pjum_position_user_id', $txtUser, PDO::PARAM_STR);

                    $sqlQuery->execute();

                    $db->commit();

                    if ($sqlQuery->rowCount() > 0) {
                        $db->beginTransaction();

                        $status_ref = 2;

                        $sqlQuery = $db->prepare("delete from t_pjum_status_tab where t_pjum_id=:t_pjum_id and pjum_position_user_id=:pjum_position_user_id and status_ref=:status_ref");

                        $sqlQuery->bindParam(':t_pjum_id', $txtKode, PDO::PARAM_STR);
                        $sqlQuery->bindParam(':pjum_position_user_id', $row[0], PDO::PARAM_STR);
                        $sqlQuery->bindParam(':status_ref', $status_ref, PDO::PARAM_STR);

                        $sqlQuery->execute();

                        $db->commit();

                        //                            if($sqlQuery->rowCount() > 0){
                        $db->beginTransaction();

                        $sqlQuery = $db->prepare("insert t_pjum_status_tab(t_pjum_id, pjum_position_user_id, status_ref) values(:t_pjum_id, :pjum_position_user_id, :status_ref)");

                        $sqlQuery->bindParam(':t_pjum_id', $txtKode, PDO::PARAM_STR);
                        $sqlQuery->bindParam(':pjum_position_user_id', $row[0], PDO::PARAM_STR);
                        $sqlQuery->bindParam(':status_ref', $status_ref, PDO::PARAM_STR);

                        $sqlQuery->execute();

                        $db->commit();

                        if ($sqlQuery->rowCount() > 0) {
                            $mail = new PHPMailer();
                            $mail->IsSMTP();
                            $mail->Host = $host_email;
                            $mail->SMTPAuth = true;
                            $mail->SMTPSecure = "tls";
                            $mail->Port     = 587;
                            $mail->Username = $username;
                            $mail->Password = $password_host_email;
                            $mail->AddAddress($row[1], $row[1]);
                            $mail->From = $from;
                            $mail->FromName = $from_name;
                            $mail->IsHTML(true);
                            $mail->Subject = $txtKode . " - Persetujuan Pertanggungjawaban Uang Muka";
                            $mail->Body    = "Untuk melakukan persetujuan pertanggungjawaban uang muka klik alamat di bawah ini :<br><a href='$internalLink/uangmuka/persetujuan_pjum.php?id=" . base64_encode($txtKode) . "&param=" . base64_encode($row[2]) . "&u=" . base64_encode($row[0]) . "&act=2'>Link Internal Sistem Uang Muka</a>" . "<br><br><a href='$externalLink/uangmuka/persetujuan_pjum.php?id=" . base64_encode($txtKode) . "&param=" .  base64_encode($row[2]) . "&u=" .  base64_encode($row[0]) . "&act=2'>Link External Sistem Uang Muka</a>";
                            $mail->Send();

                            $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
                        } else {
                            $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
                        }

                        //header('location:permintaan_overview.php');
                        //                            }else{
                        //                                $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
                        //                            }
                    } else {
                        $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
                    }
                }
            }
            //            }elseif($txtAct==2){
            //                $query = "select pj_pengadaan_disiapkan_id, pj_pengadaan_disiapkan_email, tgl_pjum from t_um_rpt where t_pjum_id = '".$txtKode."' and tgl_pjum = '".$txtParam."'";
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
            //                        $sqlQuery = $db->prepare("update t_pjum_status_tab set pjum_status = :pjum_status, tgl_status = now() where t_pjum_id = :t_pjum_id and pjum_position_user_id = :pjum_position_user_id");
            //
            //                        $sqlQuery->bindParam(':pjum_status', $txtStatus, PDO::PARAM_STR);
            //                        $sqlQuery->bindParam(':t_pjum_id', $txtKode, PDO::PARAM_STR);
            //                        $sqlQuery->bindParam(':pjum_position_user_id', $txtUser, PDO::PARAM_STR);
            //
            //                        $sqlQuery->execute();
            //
            //                        $db->commit();
            //                        
            //                        if($sqlQuery->rowCount() > 0){
            //                            $db->beginTransaction();
            //                        
            //                            $status_ref = 3;
            //
            //                            $sqlQuery = $db->prepare("delete from t_pjum_status_tab where t_pjum_id=:t_pjum_id and pjum_position_user_id=:pjum_position_user_id and status_ref=:status_ref");
            //
            //                            $sqlQuery->bindParam(':t_pjum_id', $txtKode, PDO::PARAM_STR);
            //                            $sqlQuery->bindParam(':pjum_position_user_id', $row[0], PDO::PARAM_STR);
            //                            $sqlQuery->bindParam(':status_ref', $status_ref, PDO::PARAM_STR);
            //
            //                            $sqlQuery->execute();
            //
            //                            $db->commit();
            //                            
            ////                            if($sqlQuery->rowCount() > 0){
            //                                $db->beginTransaction();
            //                        
            //                                $sqlQuery = $db->prepare("insert t_pjum_status_tab(t_pjum_id, pjum_position_user_id, status_ref) values(:t_pjum_id, :pjum_position_user_id, :status_ref)");
            //
            //                                $sqlQuery->bindParam(':t_pjum_id', $txtKode, PDO::PARAM_STR);
            //                                $sqlQuery->bindParam(':pjum_position_user_id', $row[0], PDO::PARAM_STR);
            //                                $sqlQuery->bindParam(':status_ref', $status_ref, PDO::PARAM_STR);
            //
            //                                $sqlQuery->execute();
            //
            //                                $db->commit();
            //
            //                                if($sqlQuery->rowCount() > 0){
            //                                    $mail = new PHPMailer();
            //                                    $mail->IsSMTP();              
            //                                    $mail->Host = $host_email;  
            //                                    $mail->SMTPAuth = true;     
            //                                    $mail->SMTPSecure = "tls";
            //                                    $mail->Port     = 587; 
            //                                    $mail->Username = $username;  
            //                                    $mail->Password = $password_host_email; 
            //                                    $mail->AddAddress($row[1], $row[1]);
            //                                    $mail->From = $from;
            //                                    $mail->FromName = $from_name;
            //                                    $mail->IsHTML(true);
            //                                    $mail->Subject = $txtKode." - Persetujuan Pertanggungjawaban Uang Muka";
            //                                    $mail->Body    = "Untuk melakukan persetujuan pertanggungjawaban uang muka klik alamat di bawah ini :<br><a href='$internalLink/uangmuka/persetujuan_pjum.php?id=".base64_encode($txtKode)."&param=".base64_encode($row[2])."&u=".base64_encode($row[0])."&act=3'>Link Internal Sistem Uang Muka</a>"."<br><br><a href='$externalLink/uangmuka/persetujuan_pjum.php?id=".base64_encode($txtKode)."&param=".  base64_encode($row[2])."&u=".  base64_encode($row[0])."&act=3'>Link External Sistem Uang Muka</a>";
            //                                    $mail->Send();
            //
            //                                    $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
            //                                }else{
            //                                    $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
            //                                }
            //
            //                                //header('location:permintaan_overview.php');
            ////                            }else{
            ////                                $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
            ////                            }
            //                        }else{
            //                            $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
            //                        }
            //                    }
            //                }
        } elseif ($txtAct == 3) {
            $query = "select pj_pengadaan_disetujui_id, pj_pengadaan_disetujui_email, tgl_pjum from t_um_rpt where t_pjum_id = '" . $txtKode . "' and tgl_pjum = '" . $txtParam . "'";

            $result = $db->prepare($query);
            $result->execute();

            $num = $result->rowCount();

            $count = 0;

            if ($num > 0) {
                while ($row = $result->fetch(PDO::FETCH_NUM)) {
                    $db->beginTransaction();

                    $txtStatus = "Approved";

                    $sqlQuery = $db->prepare("update t_pjum_status_tab set pjum_status = :pjum_status, tgl_status = now() where t_pjum_id = :t_pjum_id and pjum_position_user_id = :pjum_position_user_id");

                    $sqlQuery->bindParam(':pjum_status', $txtStatus, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':t_pjum_id', $txtKode, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':pjum_position_user_id', $txtUser, PDO::PARAM_STR);

                    $sqlQuery->execute();

                    $db->commit();

                    //                        if($sqlQuery->rowCount() > 0){
                    $db->beginTransaction();

                    $status_ref = 4;

                    $sqlQuery = $db->prepare("delete from t_pjum_status_tab where t_pjum_id=:t_pjum_id and pjum_position_user_id=:pjum_position_user_id and status_ref=:status_ref");

                    $sqlQuery->bindParam(':t_pjum_id', $txtKode, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':pjum_position_user_id', $row[0], PDO::PARAM_STR);
                    $sqlQuery->bindParam(':status_ref', $status_ref, PDO::PARAM_STR);

                    $sqlQuery->execute();

                    $db->commit();

                    if ($sqlQuery->rowCount() > 0) {
                        $db->beginTransaction();

                        $sqlQuery = $db->prepare("insert t_pjum_status_tab(t_pjum_id, pjum_position_user_id, status_ref) values(:t_pjum_id, :pjum_position_user_id, :status_ref)");

                        $sqlQuery->bindParam(':t_pjum_id', $txtKode, PDO::PARAM_STR);
                        $sqlQuery->bindParam(':pjum_position_user_id', $row[0], PDO::PARAM_STR);
                        $sqlQuery->bindParam(':status_ref', $status_ref, PDO::PARAM_STR);

                        $sqlQuery->execute();

                        $db->commit();
                        if ($sqlQuery->rowCount() > 0) {
                            $mail = new PHPMailer();
                            $mail->IsSMTP();
                            $mail->Host = $host_email;
                            $mail->SMTPAuth = true;
                            $mail->SMTPSecure = "tls";
                            $mail->Port     = 587;
                            $mail->Username = $username;
                            $mail->Password = $password_host_email;
                            $mail->AddAddress($row[1], $row[1]);
                            $mail->From = $from;
                            $mail->FromName = $from_name;
                            $mail->IsHTML(true);
                            $mail->Subject = $txtKode . " - Persetujuan Pertanggungjawaban Uang Muka";
                            $mail->Body    = "Untuk melakukan persetujuan pertanggungjawaban uang muka klik alamat di bawah ini :<br><a href='$internalLink/uangmuka/persetujuan_pjum.php?id=" . base64_encode($txtKode) . "&param=" . base64_encode($row[2]) . "&u=" . base64_encode($row[0]) . "&act=4'>Link Internal Sistem Uang Muka</a>" . "<br><br><a href='$externalLink/uangmuka/persetujuan_pjum.php?id=" . base64_encode($txtKode) . "&param=" .  base64_encode($row[2]) . "&u=" .  base64_encode($row[0]) . "&act=4'>Link External Sistem Uang Muka</a>";
                            $mail->Send();

                            $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
                        } else {
                            $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
                        }
                    } else {
                        $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
                    }
                    //                        }else{
                    //                            $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
                    //                        }
                }
            }
            //            }elseif($txtAct==4){
        } elseif ($txtAct == 2 || $txtAct == 4) {
            $db->beginTransaction();

            $txtStatus = "Approved";

            $sqlQuery = $db->prepare("update t_pjum_status_tab set pjum_status = :pjum_status, tgl_status = now() where t_pjum_id = :t_pjum_id and pjum_position_user_id = :pjum_position_user_id");

            $sqlQuery->bindParam(':pjum_status', $txtStatus, PDO::PARAM_STR);
            $sqlQuery->bindParam(':t_pjum_id', $txtKode, PDO::PARAM_STR);
            $sqlQuery->bindParam(':pjum_position_user_id', $txtUser, PDO::PARAM_STR);

            $sqlQuery->execute();

            $db->commit();

            if ($sqlQuery->rowCount() > 0) {
                $db->beginTransaction();

                $txtStatus = "Verification";

                $sqlQuery = $db->prepare("update t_pjum_tab set status_pjum = :status_pjum where t_pjum_id = :t_pjum_id");

                $sqlQuery->bindParam(':t_pjum_id', $txtKode, PDO::PARAM_STR);
                $sqlQuery->bindParam(':status_pjum', $txtStatus, PDO::PARAM_STR);

                $sqlQuery->execute();

                $db->commit();

                if ($sqlQuery->rowCount() > 0) {
                    $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
                } else {
                    $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
                }
            } else {
                $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
            }
        } elseif ($txtAct == 5) {
            $query = "select keuangan_penyetuju_2_id, keuangan_penyetuju_2_email, tgl_um from t_um_rpt where t_um_id = '" . $txtKode . "' and tgl_um = '" . $txtParam . "' and keuangan_penyetuju_2 is not null";

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

                    if ($sqlQuery->rowCount() > 0) {
                        $db->beginTransaction();

                        $sqlQuery = $db->prepare("insert t_um_status_tab(t_um_id, um_position_user_id) values(:t_um_id, :um_position_user_id)");

                        $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
                        $sqlQuery->bindParam(':um_position_user_id', $row[0], PDO::PARAM_STR);

                        $sqlQuery->execute();

                        $db->commit();

                        if ($sqlQuery->rowCount() > 0) {
                            $mail = new PHPMailer();
                            $mail->IsSMTP();
                            $mail->Host = $host_email;
                            $mail->SMTPAuth = true;
                            $mail->SMTPSecure = "tls";
                            $mail->Port     = 587;
                            $mail->Username = $username;
                            $mail->Password = $password_host_email;
                            $mail->AddAddress($row[1], $row[1]);
                            $mail->From = $from;
                            $mail->FromName = $from_name;
                            $mail->IsHTML(true);
                            $mail->Subject = "Persetujuan Pertanggungjawaban Uang Muka No : " . $txtKode;
                            $mail->Body    = "Untuk melakukan persetujuan pertanggungjawaban uang muka klik alamat di bawah ini :<br><a href='$internalLink/uangmuka/persetujuan_pjum.php?id=" . base64_encode($txtKode) . "&param=" . base64_encode($row[2]) . "&u=" . base64_encode($row[0]) . "&act=6'>Link Internal Sistem Uang Muka</a>" . "<br><br><a href='$externalLink/uangmuka/persetujuan_pjum.php?id=" . base64_encode($txtKode) . "&param=" .  base64_encode($row[2]) . "&u=" .  base64_encode($row[0]) . "&act=6'>Link External Sistem Uang Muka</a>";
                            $mail->Send();

                            $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
                        } else {
                            $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
                        }
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

                if ($sqlQuery->rowCount() > 0) {
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

            if ($sqlQuery->rowCount() > 0) {
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

if (isset($_POST['btnReject']) and $_SERVER['REQUEST_METHOD'] == "POST") {
    try {
        $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

        $txtAlasan = $_POST['txtAlasan'];

        if ($txtAlasan == "") {
            $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'><strong>Untuk melakukan Reject, Alasan Reject wajib diisi !!</strong></div>";
        } else {
            $db->beginTransaction();

            $txtStatus = "Rejected";
            $status_ref = 0;

            $sqlQuery = $db->prepare("update t_pjum_status_tab set pjum_status = :pjum_status, tgl_status = now(), status_ref = :status_ref where t_pjum_id = :t_pjum_id and pjum_position_user_id = :pjum_position_user_id");

            $sqlQuery->bindParam(':pjum_status', $txtStatus, PDO::PARAM_STR);
            $sqlQuery->bindParam(':t_pjum_id', $txtKode, PDO::PARAM_STR);
            $sqlQuery->bindParam(':status_ref', $status_ref, PDO::PARAM_STR);
            $sqlQuery->bindParam(':pjum_position_user_id', $txtUser, PDO::PARAM_STR);

            $sqlQuery->execute();

            $db->commit();

            $db->beginTransaction();

            $status_ref = 0;

            $sqlQuery = $db->prepare("update t_pjum_status_tab set status_ref = :status_ref where t_pjum_id = :t_pjum_id");

            $sqlQuery->bindParam(':t_pjum_id', $txtKode, PDO::PARAM_STR);
            $sqlQuery->bindParam(':status_ref', $status_ref, PDO::PARAM_STR);

            $sqlQuery->execute();

            $db->commit();

            $db->beginTransaction();

            $txtStatus = "Draft";

            $sqlQuery = $db->prepare("update t_pjum_tab set status_pjum = :status_pjum, note_reject = :note_reject where t_pjum_id = :t_pjum_id");

            $sqlQuery->bindParam(':t_pjum_id', $txtKode, PDO::PARAM_STR);
            $sqlQuery->bindParam(':status_pjum', $txtStatus, PDO::PARAM_STR);
            $sqlQuery->bindParam(':note_reject', $txtAlasan, PDO::PARAM_STR);

            $sqlQuery->execute();

            $db->commit();

            if ($sqlQuery->rowCount() > 0) {
                $query = "SELECT a.pj_user_peminta_email FROM t_um_rpt a WHERE a.t_pjum_id = '$txtKode'";
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
                $mail->Host = $host_email;
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = "tls";
                $mail->Port     = 587;
                $mail->Username = $username;
                $mail->Password = $password_host_email;
                $mail->AddAddress($txtEmail, $txtEmail);
                $mail->From = $from;
                $mail->FromName = $from_name;
                $mail->IsHTML(true);
                $mail->Subject = "Pertanggungjawaban Uang Muka No : " . $txtKode . " Ditolak";
                $mail->Body    = "Pertanggungjawaban uang muka anda nomor : " . $txtKode . " ditolak.";
                $mail->Send();

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

    $query = "select * from t_um_rpt where t_pjum_id='$txtKode'";
    $result = $db->prepare($query);
    $result->execute();

    $num = $result->rowCount();

    if ($num > 0) {
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $txtKeperluan = $row[2];
            $txtPeriode = $row[6];
            $txtLampiran = $row[5];
            $txtLampiran2 = $row[45];
            $cmbUser1 = $row[56] . " - " . $row[57] . " (" . $row[86] . " " . $row[87] . ")";
            $cmbUser2 = $row[58] . " - " . $row[59] . " (" . $row[88] . " " . $row[89] . ")";
            $cmbPengadaan1 = $row[60] . " - " . $row[61] . " (" . $row[90] . " " . $row[91] . ")";
            $cmbPengadaan2 = $row[62] . " - " . $row[63] . " (" . $row[92] . " " . $row[93] . ")";
            $txtNilaiUM = number_format($row[21], 2);
            $txtEvaluasiNilaiUM = number_format($row[30], 2);
            $txtNilaiPJUM = number_format($row[52], 2);
            $txtSisa = number_format($row[55], 2);
            $txtKelebihan = $row[85];

            $approvalStatus = "";

            if ($txtAct == 1) {
                $approvalStatus = $row[86];
            } elseif ($txtAct == 2) {
                $approvalStatus = $row[88];
            } elseif ($txtAct == 3) {
                $approvalStatus = $row[90];
            } elseif ($txtAct == 4) {
                $approvalStatus = $row[92];
            }

            $txtAlasan = $row[97];
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

    $query = "select * from t_um_detail_tab where t_um_id=(select t_um_id from t_pjum_tab where t_pjum_id = '$txtKode') order by t_um_detail_id";
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
            $str = $str . "<td valign='top'>" . number_format($row[6], 2) . "</td>";
            $str = $str . "<td valign='top'>" . number_format($row[7], 2) . "</td>";
            $str = $str . "<td valign='top'>" . number_format($row[8], 2) . "</td>";
            $str = $str . "<td valign='top'>" . number_format($row[9], 2) . "</td>";
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
                <h2 class="with-border">Persetujuan Pertanggujawaban Uang Muka : <?PHP echo $txtKode; ?></h2>
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
                        <label class="col-sm-2 form-control-label">Nilai PJUM *</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><?PHP echo $txtNilaiPJUM; ?></p>
                        </div>
                        <label class="col-sm-2 form-control-label">Sisa / Kurang *</label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><?PHP echo $txtSisa; ?></p>
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
                        <label class="col-sm-2 form-control-label">Lampiran PJUM</label>
                        <div class="col-sm-10">
                            <div class="form-group">
                                <div class="input-group">
                                    <?PHP if ($txtLampiran2 == "") {
                                        echo "<span class='color-red'>Lampiran belum diupload !!</span>";
                                    } else {
                                        echo "<a href='files/$txtLampiran2' target='_blank'>" . "Download Lampiran</a>";
                                    } ?>
                                </div>
                            </div>
                        </div>
                        <label class="col-sm-2 form-control-label">Bukti Transfer Pengembalian Kelebihan UM</label>
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

                    <div class="box-typical box-typical-padding">
                        <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Rincian</th>
                                    <th>Qty Evaluasi</th>
                                    <th>Harga Evaluasi</th>
                                    <th>Qty PJ</th>
                                    <th>Harga PJ</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>No</th>
                                    <th>Rincian</th>
                                    <th>Qty Evaluasi</th>
                                    <th>Harga Evaluasi</th>
                                    <th>Qty PJ</th>
                                    <th>Harga PJ</th>
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
                        echo "<div class='col-sm-12 alert alert-success'><strong>Anda sudah melakukan " . $approvalStatus . " untuk pertanggungjawaban uang muka ini</strong></div>";
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