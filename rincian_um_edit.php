<?PHP
    session_start();

    if (!isset($_SESSION['sessunameuangmuka'])) {
        header('location:index.php');
    }

    include "koneksi/connect-db.php";
    include "mailer/class.PHPMailer.php";
    
    $modeDebug = 0;
    $strMessage = "";
    
    $txtKode = base64_decode($_GET['id2']);  
    $txtKodeDetail = base64_decode($_GET['id']);
    $txtRincian = "";            
    $txtQty = ""; 
    $txtHarga = "";
    
    // TAMBAH
    if (isset($_POST['btnSimpan']) and $_SERVER['REQUEST_METHOD'] == "POST") {
        try {
            $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

            $txtRincian = $_POST['txtRincian'];           
            $txtQty = $_POST['txtQty']; 
            $txtHarga = $_POST['txtHarga'];

            if($txtRincian=="" || $txtQty=="" || $txtHarga==""){
                $strMessage = "<div class='alert alert-error'><strong>Kolom dengan tanda bintang (*) wajib diisi</strong></div>";
            }else{
                $db->beginTransaction();

                $sqlQuery = $db->prepare("update t_um_detail_tab set rincian = :rincian, qty_pengajuan = :qty_pengajuan, harga_pengajuan = :harga_pengajuan where t_um_detail_id = :t_um_detail_id and t_um_id = :t_um_id");

                $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
                $sqlQuery->bindParam(':t_um_detail_id', $txtKodeDetail, PDO::PARAM_STR);
                $sqlQuery->bindParam(':rincian', $txtRincian, PDO::PARAM_STR);
                $sqlQuery->bindParam(':qty_pengajuan', $txtQty, PDO::PARAM_STR);
                $sqlQuery->bindParam(':harga_pengajuan', $txtHarga, PDO::PARAM_STR);

                $sqlQuery->execute();

                $db->commit();

                if($sqlQuery->rowCount() > 0){
                    header('location:permintaan_edit.php?id='.base64_encode($txtKode));

                    //header('location:home.php');
                    //$strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
                }else{
                    $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
                }
            }
        }catch(PDOException $e){
            if($modeDebug==0){
                $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Oops, there is something wrong.....</div>";
            }else{
                $strMessage = $e->getMessage();
            }
        }
    }

    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
        
        $query = "select * from t_um_detail_tab where t_um_id='$txtKode' and t_um_detail_id = '$txtKodeDetail'";
        $result = $db->prepare($query);
        $result->execute();

        $num = $result->rowCount();
        
        if($num > 0) {
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $txtRincian = $row[2];            
                $txtQty = $row[4]; 
                $txtHarga = $row[5];
            }
        }
        
        $db = null;
    } catch (Exception $e) {
        if($modeDebug==0){
            $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Oops, there is something wrong.....</div>";
        }else{
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
	        <?PHP include "menu_up.php";?>
	    </div><!--.container-fluid-->
	</header><!--.site-header-->

	<div class="mobile-menu-left-overlay"></div>
	<?PHP include "menu_left.php";?>

        <div class="page-content">
            <?PHP echo $strMessage;?>
            <div class="container-fluid">
                <div class="box-typical box-typical-padding">
                    <h2 class="with-border">Rincian Uang Muka : <?PHP echo $txtKode;?></h2>
                    <form id="frmRincian" action="" method="post" enctype="multipart/form-data">
                        <div class="form-group row">
                            <label class="col-sm-2 form-control-label">Rincian *</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><input type="text" class="form-control" name="txtRincian" id="txtRincian" value="<?PHP echo $txtRincian;?>"></p>
                            </div>
                            <label class="col-sm-2 form-control-label">Qty *</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><input type="text" class="form-control" name="txtQty" id="txtQty" value="<?PHP echo $txtQty;?>"></p>
                            </div>
                            <label class="col-sm-2 form-control-label">Harga *</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><input type="text" class="form-control" name="txtHarga" id="txtHarga" value="<?PHP echo $txtHarga;?>"></p>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-inline" name="btnSimpan" id="btnSimpan">Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div><!--.page-content-->

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
                $('#txtTglTransaksi').daterangepicker({
                    singleDatePicker: true,
                    showDropdowns: true
                });
            });
	</script>
        
        <script src="js/lib/datatables-net/datatables.min.js"></script>
        <script>
		$(function() {
			$('#example').DataTable({
                            "order": [[ 0, "asc" ]]
                        });
		});
	</script>
        
<script src="js/app.js"></script>
</body>
</html>