<?PHP
    session_start();

    if (!isset($_SESSION['sessunameuangmuka'])) {
        header('location:index.php');
    }

    include "koneksi/connect-db.php";
    
    $modeDebug = 0;
    $strMessage = "";
    
    $str = "";
    
    $strLock = "";
    
    try {
        $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
        
        $query = "SELECT count(t_um_id) FROM lock_aging_rpt where divisi = ".$_SESSION['sessdivisiunameuangmuka'];
        
        $result = $db->prepare($query);
        $result->execute();     

        $num = $result->rowCount();

        $count = 0;

        if($num > 0) {
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                if($row[0] >= 1){
                    $strLock = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Divisi anda terkena Lock Aging, cek UM yang belum dibuat PJUM dan PJUM belum Approved di halaman Home</div>";
                }
            }
        }
    } catch (Exception $e) {
        if($modeDebug==0){
            $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Oops, there is something wrong.....</div>";
        }else{
            $strMessage = $e->getMessage();
        }
    }
    
    try {
        $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
        
        if($_SESSION['sessevaluangmuka']==1 || $_SESSION['sesspencairanuangmuka']==1){
            $query = "SELECT t_um_id, tgl_um, keperluan, status_um, nilai_um, evaluasi_nilai_um, tgl_diterima, t_pjum_id, divisions.NAME AS nama_divisi from t_um_rpt LEFT JOIN divisions ON t_um_rpt.divisi=divisions.id order by tgl_um";
        }else{
            $query = "SELECT t_um_id, tgl_um, keperluan, status_um, nilai_um, evaluasi_nilai_um, tgl_diterima, t_pjum_id, divisions.NAME AS nama_divisi from t_um_rpt LEFT JOIN divisions ON t_um_rpt.divisi=divisions.id where divisi = ".$_SESSION['sessdivisiunameuangmuka']." OR um_divisi_pembuat = (SELECT x.division_id FROM users x WHERE id = ".$_SESSION['sessiduangmuka'].") or um_user_pembuat = ".$_SESSION['sessiduangmuka']." order by tgl_um";
        }
        
        $result = $db->prepare($query);
        $result->execute();     

        $num = $result->rowCount();

        $count = 0;

        if($num > 0) {
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $count = $count + 1;

                $str = $str."<tr>";
                $str = $str."<td valign='top'>".$row[0]."</td>";
                $str = $str."<td valign='top'>".$row[1]."</td>";
                $str = $str."<td valign='top'>".$row[8]."</td>";
                $str = $str."<td valign='top'>".$row[2]."</td>";
                $str = $str."<td valign='top'>".$row[3]."</td>";
                $str = $str."<td valign='top'>".number_format($row[4],2)."</td>";
                $str = $str."<td valign='top'>".number_format($row[5],2)."</td>";
                $str = $str."<td valign='top'>".$row[6]."</td>";
                
                $row[0] = base64_encode($row[0]);
                
                if($row[3]=="Draft"){
                    $str = $str."<td class='center'>
                                <a class='btn btn-success' href='permintaan_edit.php?id=$row[0]'>
                                        <i class='icon-edit icon-white'></i>  
                                        Edit                                            
                                </a>
                        </td>";
                }else{
                    $str = $str."<td valign='top'></td>";
                }
                
                $str = $str."<td class='center'>
                                <a class='btn btn-success' href='permintaan_view.php?id=$row[0]'>
                                        <i class='icon-edit icon-white'></i>  
                                        View                                            
                                </a>
                        </td>";
                
                if($row[3]=="Received by User"){
                    if($row[7]==""){
                        $str = $str."<td class='center'>
                                <a class='btn btn-success' href='pjum.php?id=$row[0]'>
                                        <i class='icon-edit icon-white'></i>  
                                        PJUM                                            
                                </a>
                        </td>";
                    }else{
                        $str = $str."<td valign='top'></td>";
                    }
                }else{
                    $str = $str."<td valign='top'></td>";
                }
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
            <?PHP echo $strLock;?>
            <div class="container-fluid">
                <div class="box-typical box-typical-padding">
                    <a class="btn btn-primary" href="permintaan.php">
                        <i class="icon-edit icon-white"></i>  
                        Buat Permintaan UM                                          
                    </a>
                </div>
                <div class="box-typical box-typical-padding">
                    <h2 class="with-border"><?PHP echo "<a href='print_excel_permintaan.php' target='_blank'><i class='fa fa-file-excel-o'></i> Export Excel</a>";?></h2>
                    <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <!--<th>No</th>-->
                            <th>Kode</th>
                            <th>Tgl UM</th>
                            <th>Divisi</th>
                            <th>Keperluan</th>
                            <th>Status</th>
                            <th>Diajukan</th>
                            <th>Dievaluasi</th>
                            <th>Diterima</th>
                            <th>Edit</th>
                            <th>View</th>
                            <th>PJUM</th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <!--<th>No</th>-->
                            <th>Kode</th>
                            <th>Tgl UM</th>
                            <th>Divisi</th>
                            <th>Keperluan</th>
                            <th>Status</th>
                            <th>Diajukan</th>
                            <th>Dievaluasi</th>
                            <th>Diterima</th>
                            <th>Edit</th>
                            <th>View</th>
                            <th>PJUM</th>
                        </tr>
                        </tfoot>
                        <tbody>
                            <?PHP echo $str;?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!--.page-content-->
		<?php echo "test " . $_SESSION['sessdivisiunameuangmuka'] ." - ". $_SESSION['sessiduangmuka']; ?>
		

	<script src="js/lib/jquery/jquery-3.2.1.min.js"></script>
	<script src="js/lib/popper/popper.min.js"></script>
	<script src="js/lib/tether/tether.min.js"></script>
	<script src="js/lib/bootstrap/bootstrap.min.js"></script>
	<script src="js/plugins.js"></script>
        <script src="js/lib/datatables-net/datatables.min.js"></script>
        <script>
		$(function() {
			$('#example2').DataTable({
                            "processing": true,
                            "serverSide": true,
                            "ajax": "getDaftarPermintaan.php"
                        });
            
                        $('#example').DataTable({
                            "order": [[ 0, "desc" ]]
                        });
		});
	</script>
        
<script src="js/app.js"></script>
</body>
</html>