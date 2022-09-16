<?PHP
    session_start();

    if (!isset($_SESSION['sessunameuangmuka'])) {
        header('location:index.php');
    }

    include "koneksi/connect-db.php";
    
    $modeDebug = 0;
    $strMessage = "";
    
    $txtKode = base64_decode($_GET['id']);    
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
            
            if($txtQty=="" || $txtHarga==""){
                $strMessage = "<div class='alert alert-error'><strong>Kolom dengan tanda bintang (*) wajib diisi</strong></div>";
            }else{
                $db->beginTransaction();

                $sqlQuery = $db->prepare("insert into t_um_detail_tab(t_um_id, rincian, qty_pengajuan, harga_pengajuan) values(:t_um_id, :rincian, :qty_pengajuan, :harga_pengajuan)");

                $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
                $sqlQuery->bindParam(':rincian', $txtRincian, PDO::PARAM_STR);
                $sqlQuery->bindParam(':qty_pengajuan', $txtQty, PDO::PARAM_STR);
                $sqlQuery->bindParam(':harga_pengajuan', $txtHarga, PDO::PARAM_STR);

                $sqlQuery->execute();

                $db->commit();

                if($sqlQuery->rowCount() > 0){
                    //header('location:edit_aset.php?add=true&id='.base64_encode($txtKode));
                    $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
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
    
    $str = "";
    
    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

        $query = "select * from t_um_detail_tab where t_um_id='$txtKode' order by t_um_detail_id";
        $result = $db->prepare($query);
        $result->execute();     

        $num = $result->rowCount();

        $count = 0;

        if($num > 0) {
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $count = $count + 1;

                $str = $str."<tr>";
                $str = $str."<td valign='top'>".$count."</td>";
                $str = $str."<td valign='top'>".$row[2]."</td>";
                $str = $str."<td valign='top'>".number_format($row[4],2)."</td>";
                $str = $str."<td valign='top'>".number_format($row[5],2)."</td>";
                $str = $str."<td valign='top'>".number_format($row[6],2)."</td>";
                $str = $str."<td valign='top'>".number_format($row[7],2)."</td>";
                
                $row[0] = base64_encode($row[0]);
                $row[1] = base64_encode($row[1]);
                
                
                $str = $str."<td class='center'>
                            <a class='btn btn-success' href='evaluasi_rincian_um_edit.php?id=$row[0]&id2=$row[1]'>
                                    <i class='icon-edit icon-white'></i>  
                                    Edit                                            
                            </a>
                    </td>";
            }
        }
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
                    <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Rincian</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Qty Evaluasi</th>
                            <th>Harga Evaluasi</th>
                            <th>Edit</th>
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
                            <th>Edit</th>
                        </tr>
                        </tfoot>
                        <tbody>
                            <?PHP echo $str;?>
                        </tbody>
                    </table>
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