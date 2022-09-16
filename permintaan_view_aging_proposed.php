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
$txtKode2 = "";

//if (isset($_POST['btnPropose']) and $_SERVER['REQUEST_METHOD'] == "POST") {
try {
    $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

    $query = "select user_peminta_id, user_peminta_email, tgl_um, t_um_id from t_um_rpt where t_um_id = (select t_um_id from t_dispensasi_tab where t_dispensasi_id = '" . $txtKode . "')";
    $result = $db->prepare($query);
    $result->execute();

    $num = $result->rowCount();

    $count = 0;

    if ($num > 0) {
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $db->beginTransaction();

            $txtKode2 = $row[3];

            $status_ref = 1;

            $sqlQuery = $db->prepare("delete from t_dispensasi_status_tab where t_dispensasi_id=:t_dispensasi_id and status_ref=:status_ref");

            $sqlQuery->bindParam(':t_dispensasi_id', $txtKode, PDO::PARAM_STR);
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
                $db->beginTransaction();

                $txtStatProposed = "Proposed";

                $sqlQuery = $db->prepare("update t_dispensasi_tab set status_perpanjangan = :status_perpanjangan where t_dispensasi_id = :t_dispensasi_id");

                $sqlQuery->bindParam(':t_dispensasi_id', $txtKode, PDO::PARAM_STR);
                $sqlQuery->bindParam(':status_perpanjangan', $txtStatProposed, PDO::PARAM_STR);

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
                    $mail->Subject = "Persetujuan Perpanjangan Pertanggungjawaban Uang Muka No : " . $txtKode;
                    $mail->Body    = "Untuk melakukan persetujuan perpanjangan pertanggungjawaban uang muka klik alamat di bawah ini :<br><a href='$internalLink/uangmuka/persetujuan_pp.php?id=" . base64_encode($txtKode) . "&param=" . base64_encode($row[2]) . "&u=" . base64_encode($row[0]) . "&act=1'>Link Internal Sistem Uang Muka</a>" . "<br><br><a href='$externalLink/uangmuka/persetujuan_pp.php?id=" . base64_encode($txtKode) . "&param=" .  base64_encode($row[2]) . "&u=" .  base64_encode($row[0]) . "&act=1'>Link External Sistem Uang Muka</a>";
                    $mail->Send();
                }

                header('location:permintaan_view_aging.php?id=' . base64_encode($txtKode2));
                //$strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
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
    //}
