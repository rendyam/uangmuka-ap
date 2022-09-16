<?PHP
    session_start();

    if (!isset($_SESSION['sessunameuangmuka'])) {
        header('location:index.php');
    }

    include "koneksi/connect-db.php";
    
    $modeDebug = 0;
    $strMessage = "";
    
    $txtKode = base64_decode($_GET['id2']); 
    $txtKodeDetail = base64_decode($_GET['id']);
    
    if (isset($_GET['del']) and $_SERVER['REQUEST_METHOD'] == "GET") {
        try {
            $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

            $db->beginTransaction();

            $sqlQuery = $db->prepare("delete from t_um_detail_tab where t_um_id = :t_um_id and t_um_detail_id = :t_um_detail_id");

            $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
            $sqlQuery->bindParam(':t_um_detail_id', $txtKodeDetail, PDO::PARAM_STR);

            $sqlQuery->execute();

            $db->commit();

            if($sqlQuery->rowCount() > 0){
                header('location:rincian_um.php?id='.base64_encode($txtKode));
            }else{
                $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
            }
        }catch(PDOException $e){
            if($modeDebug==0){
                $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Oops, there is something wrong.....</div>";
            }else{
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
    
    <link rel="stylesheet" href="css/separate/vendor/bootstrap-daterangepicker.min.css">
</head>
</html>