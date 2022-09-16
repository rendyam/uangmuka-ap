<?PHP
    session_start();

    if (!isset($_SESSION['sessunameuangmuka'])) {
        header('location:index.php');
    }

    include "koneksi/connect-db.php";
    
    $strMessage = "";
    
    $str = "";
    
    try {
        $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
        
        if($_SESSION['sessevaluangmuka']==1){
            $query = "SELECT t_um_id, tgl_um, keperluan, status_um, nilai_um, evaluasi_nilai_um, tgl_diterima, t_pjum_id, nilai_pjum, aging, (SELECT NAME FROM divisions WHERE id = um_divisi_pembuat) AS divisi from t_um_rpt where status_um = 'Received by User' and (status_pjum <> 'Approved' or status_pjum is null) order by tgl_um";
        }else{
            $query = "SELECT t_um_id, tgl_um, keperluan, status_um, nilai_um, evaluasi_nilai_um, tgl_diterima, t_pjum_id, nilai_pjum, aging, (SELECT NAME FROM divisions WHERE id = um_divisi_pembuat) AS divisi from t_um_rpt where (divisi = ".$_SESSION['sessdivisiunameuangmuka']." OR (SELECT id FROM divisions WHERE id = um_divisi_pembuat) = ".$_SESSION['sessdivisiunameuangmuka'].") and status_um = 'Received by User' and (status_pjum <> 'Approved' or status_pjum is null) order by tgl_um";
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
                $str = $str."<td valign='top'>".$row[10]."</td>";
                $str = $str."<td valign='top'>".$row[1]."</td>";
                $str = $str."<td valign='top'>".$row[2]."</td>";
                $str = $str."<td valign='top'>".$row[6]."</td>";
                $str = $str."<td valign='top'>".$row[9]."</td>";

                $row[0] = base64_encode($row[0]);

                $str = $str."<td class='center'>
                                <a class='btn btn-success' href='permintaan_view_aging.php?id=$row[0]'>
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
<?PHP include "header.php";?>
<body class="with-side-menu">

	<header class="site-header">
	    <div class="container-fluid">
                
                <!logo startui-->
	        <!--<a href="#" class="site-logo">
	            <img class="hidden-md-down" src="img/logo-2.png" alt="">
	            <img class="hidden-lg-down" src="img/logo-2-mob.png" alt="">
	        </a>-->
                
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
                    <h2 class="with-border">Aging UM</h2>
                    <h2 class="with-border"><?PHP echo "<a href='print_excel_aging_um_user.php' target='_blank'><i class='fa fa-file-excel-o'></i> Export Excel</a>";?></h2>
                    <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <!--<th>No</th>-->
                            <th>Kode</th>
                            <th>Divisi</th>
                            <th>Tgl UM</th>
                            <th>Keperluan</th>
                            <th>Diterima</th>
                            <th>Aging (hari)</th>
                            <th>View</th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <!--<th>No</th>-->
                            <th>Kode</th>
                            <th>Divisi</th>
                            <th>Tgl UM</th>
                            <th>Keperluan</th>
                            <th>Diterima</th>
                            <th>Aging (hari)</th>
                            <th>View</th>
                        </tr>
                        </tfoot>
                        <tbody>
                            <?PHP echo $str;?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
	<script src="js/lib/jquery/jquery-3.2.1.min.js"></script>
	<script src="js/lib/popper/popper.min.js"></script>
	<script src="js/lib/tether/tether.min.js"></script>
	<script src="js/lib/bootstrap/bootstrap.min.js"></script>
	<script src="js/plugins.js"></script>
        <script src="js/lib/datatables-net/datatables.min.js"></script>
        <script>
		$(function() {
                        $('#example').DataTable({
                            "order": [[ 5, "desc" ]]
                        });
		});
	</script>
        
<script src="js/app.js"></script>
</body>
</html>