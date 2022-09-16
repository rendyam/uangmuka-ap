<?PHP
    session_start();

    if (!isset($_SESSION['sessunameuangmuka'])) {
        header('location:index.php');
    }

    include "koneksi/connect-db.php";
    include "mailer/class.PHPMailer.php";
    
    $modeDebug = 0;
    
    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
        
        $txtKode = base64_decode($_GET['id']);
        
        $db->beginTransaction();

        $txtStatus = "Ready";

        $sqlQuery = $db->prepare("update t_um_tab set status_um = :status_um where t_um_id = :t_um_id");

        $sqlQuery->bindParam(':status_um', $txtStatus, PDO::PARAM_STR);
        $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);

        $sqlQuery->execute();

        $db->commit();
        
        if($sqlQuery->rowCount() > 0){
            $query = "SELECT a.user_peminta_email FROM t_um_rpt a WHERE a.t_um_id = '$txtKode'";
            $result = $db->prepare($query);
            $result->execute();

            $num = $result->rowCount();

            if($num > 0) {
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
                    $mail->Subject = "Pencairan Uang Muka No : ".$txtKode." Siap Diambil";
                    $mail->Body    = "Pencairan uang muka anda nomor : ".$txtKode." siap diambil.";
                    $mail->Send();
                }
            }
        }
    }catch(PDOException $e){
        if($modeDebug==0){
            $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Oops, there is something wrong.....</div>";
        }else{
            $strMessage = $e->getMessage();
        }
    }
    
    header('location:pencairan_overview.php');
    
?>