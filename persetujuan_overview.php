<?PHP
    session_start();

    if (!isset($_SESSION['sessunameuangmuka'])) {
        header('location:index.php');
    }

    include "koneksi/connect-db.php";
    
    $modeDebug = 0;
    $strMessage = "";
    echo $_SESSION['sessiduangmuka'];
    $str = "";
    
    try {
        $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

        $query = "SELECT a.t_um_id, b.tgl_um, b.keperluan, b.nilai_um, IF(a.status_ref = 1, b.user_peminta_id, IF(a.status_ref = 2, b.user_penyetuju_id, IF(a.status_ref = 3, b.pengadaan_disiapkan_id, IF(a.status_ref = 4, b.pengadaan_disetujui_id, IF(a.status_ref = 5, b.keuangan_penyetuju_1_id, IF(a.status_ref = 6, b.keuangan_penyetuju_2_id, 0)))))) user_id, a.status_ref, c.NAME AS nama_divisi FROM t_um_status_tab a LEFT JOIN t_um_rpt b ON a.t_um_id = b.t_um_id LEFT JOIN divisions c ON b.divisi=c.id WHERE a.um_position_user_id = ".$_SESSION['sessiduangmuka']." AND a.tgl_status IS null";
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
                $str = $str."<td valign='top'>".$row[6]."</td>";
                $str = $str."<td valign='top'>".$row[2]."</td>";
                $str = $str."<td valign='top'>".number_format($row[3],2)."</td>";
                
                $row[0] = base64_encode($row[0]);
                
                $str = $str."<td class='center'>
                                <a class='btn btn-success' href='persetujuan_um_1.php?id=".$row[0]."&param=".base64_encode($row[1])."&u=".base64_encode($row[4])."&act=".$row[5]."'>
                                        <i class='icon-edit icon-white'></i>  
                                        View                                            
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
    
    $str2 = "";
    
    try {
        $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

        $query = "SELECT a.t_pjum_id, b.tgl_pjum, b.keperluan, b.evaluasi_nilai_um, b.nilai_pjum, b.nilai_sisa, IF(a.status_ref = 1, b.pj_user_peminta_id, IF(a.status_ref = 2, b.pj_user_penyetuju_id, IF(a.status_ref = 3, b.pj_pengadaan_disiapkan_id, IF(a.status_ref = 4, b.pj_pengadaan_disetujui_id, 0)))) user_id, a.status_ref, c.NAME AS nama_divisi FROM t_pjum_status_tab a LEFT JOIN t_um_rpt b ON a.t_pjum_id = b.t_pjum_id LEFT JOIN divisions c ON b.divisi=c.id WHERE a.pjum_position_user_id = ".$_SESSION['sessiduangmuka']." AND a.tgl_status IS null";
        $result = $db->prepare($query);
        $result->execute();     

        $num = $result->rowCount();

        $count = 0;

        if($num > 0) {
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $count = $count + 1;

                $str2 = $str2."<tr>";
                $str2 = $str2."<td valign='top'>".$row[0]."</td>";
                $str2 = $str2."<td valign='top'>".$row[1]."</td>";
                $str2 = $str2."<td valign='top'>".$row[8]."</td>";
                $str2 = $str2."<td valign='top'>".number_format($row[3],2)."</td>";
                $str2 = $str2."<td valign='top'>".number_format($row[4],2)."</td>";
                $str2 = $str2."<td valign='top'>".number_format($row[5],2)."</td>";
                
                $row[0] = base64_encode($row[0]);
                
                $str2 = $str2."<td class='center'>
                                <a class='btn btn-success' href='persetujuan_pjum_1.php?id=".$row[0]."&param=".base64_encode($row[1])."&u=".base64_encode($row[6])."&act=".$row[7]."'>
                                        <i class='icon-edit icon-white'></i>  
                                        View                                            
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
    
    $str3 = "";
    
    try {
        $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

        $query = "SELECT distinct(a.t_dispensasi_id), b.tgl_dispensasi, d.name, b.t_um_id, b.tgl_perpanjangan, b.alasan_perpanjangan, IF(a.status_ref = 1, c.user_peminta_id, IF(a.status_ref = 2, c.user_penyetuju_id,  IF(a.status_ref = 3, c.keuangan_penyetuju_1_id, 0))) user_id, a.status_ref FROM t_dispensasi_status_tab a LEFT JOIN t_dispensasi_tab b ON a.t_dispensasi_id = b.t_dispensasi_id LEFT JOIN t_um_rpt c ON b.t_um_id = c.t_um_id LEFT JOIN divisions d ON c.divisi=d.id WHERE a.dispensasi_position_user_id = ".$_SESSION['sessiduangmuka']." and a.tgl_status IS NULL";
        $result = $db->prepare($query);
        $result->execute();     

        $num = $result->rowCount();

        $count = 0;

        if($num > 0) {
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $count = $count + 1;

                $str3 = $str3."<tr>";
                $str3 = $str3."<td valign='top'>".$row[0]."</td>";
                $str3 = $str3."<td valign='top'>".$row[1]."</td>";
                $str3 = $str3."<td valign='top'>".$row[2]."</td>";
                $str3 = $str3."<td valign='top'>".$row[3]."</td>";
                $str3 = $str3."<td valign='top'>".$row[4]."</td>";
                $str3 = $str3."<td valign='top'>".$row[5]."</td>";
                
                $row[0] = base64_encode($row[0]);
                
                $str3 = $str3."<td class='center'>
                                <a class='btn btn-success' href='persetujuan_pp_1.php?id=".$row[0]."&param=".base64_encode($row[1])."&u=".base64_encode($row[6])."&act=".$row[7]."'>
                                        <i class='icon-edit icon-white'></i>  
                                        View                                            
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
                    <h2 class="with-border">Persetujuan Permintaan UM</h2>
                    <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <!--<th>No</th>-->
                            <th>Kode</th>
                            <th>Tgl UM</th>
                            <th>Divisi</th>
                            <th>Keperluan</th>
                            <th>Diajukan</th>
                            <th>View</th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <!--<th>No</th>-->
                            <th>Kode</th>
                            <th>Tgl UM</th>
                            <th>Divisi</th>
                            <th>Keperluan</th>
                            <th>Diajukan</th>
                            <th>View</th>
                        </tr>
                        </tfoot>
                        <tbody>
                            <?PHP echo $str;?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="container-fluid">
                <div class="box-typical box-typical-padding">
                    <h2 class="with-border">Persetujuan Permintaan PJUM</h2>
                    <table id="example2" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <!--<th>No</th>-->
                            <th>Kode</th>
                            <th>Tgl UM</th>
                            <th>Divisi</th>
                            <th>Keperluan</th>
                            <th>Dievaluasi</th>
                            <th>Nilai PJUM</th>
                            <th>Nilai Sisa</th>
                            <th>View</th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <!--<th>No</th>-->
                            <th>Kode</th>
                            <th>Tgl UM</th>
                            <th>Divisi</th>
                            <th>Keperluan</th>
                            <th>Dievaluasi</th>
                            <th>Nilai PJUM</th>
                            <th>Nilai Sisa</th>
                            <th>View</th>
                        </tr>
                        </tfoot>
                        <tbody>
                            <?PHP echo $str2;?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="container-fluid">
                <div class="box-typical box-typical-padding">
                    <h2 class="with-border">Persetujuan Pengajuan Perpanjangan PJUM</h2>
                    <table id="example2" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <!--<th>No</th>-->
                            <th>Kode</th>
                            <th>Tgl</th>
                            <th>Divisi</th>
                            <th>Kode UM</th>
                            <th>Estimasi PJUM</th>
                            <th>Alasan</th>
                            <th>View</th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <!--<th>No</th>-->
                            <th>Kode</th>
                            <th>Tgl</th>
                            <th>Divisi</th>
                            <th>Kode UM</th>
                            <th>Estimasi PJUM</th>
                            <th>Alasan</th>
                            <th>View</th>
                        </tr>
                        </tfoot>
                        <tbody>
                            <?PHP echo $str3;?>
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
        <script src="js/lib/datatables-net/datatables.min.js"></script>
        <script>
		$(function() {
                        $('#example').DataTable({
                            "order": [[ 0, "desc" ]]
                        });
                        
                        $('#example2').DataTable({
                            "order": [[ 0, "desc" ]]
                        });
		});
	</script>
        
<script src="js/app.js"></script>
</body>
</html>