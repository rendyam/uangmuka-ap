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
    $txtNilaiUM = "";
    $txtEvaluasiNilaiUM = "";
    $txtNilaiPJUM = "";
    $txtSisa = "";
    
    if (isset($_POST['btnSimpan']) and $_SERVER['REQUEST_METHOD'] == "POST") {
        try {
            $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
            
            $keuanganDiperiksa = $_SESSION['sessiduangmuka'];
            
            $db->beginTransaction();
            
            $txtStatus = "Approved";
            
            $sqlQuery = $db->prepare("update t_pjum_tab set keuangan_diperiksa = (select xx.id from position_user_rpt xx where xx.user_id = :keuangan_diperiksa AND is_active = 1), status_pjum = :status_pjum where t_pjum_id = :t_pjum_id");

            $sqlQuery->bindParam(':t_pjum_id', $txtKode, PDO::PARAM_STR);
            $sqlQuery->bindParam(':keuangan_diperiksa', $keuanganDiperiksa, PDO::PARAM_STR);
            $sqlQuery->bindParam(':status_pjum', $txtStatus, PDO::PARAM_STR);

            $sqlQuery->execute();

            $db->commit();

            if($sqlQuery->rowCount() > 0){
                //header('location:permintaan_edit.php?id='.base64_encode($txtKode));

                //header('location:home.php');
                $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
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
    
    if (isset($_POST['btnReject']) and $_SERVER['REQUEST_METHOD'] == "POST") {
        try {
            $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
            
            $keuanganDiperiksa = $_SESSION['sessiduangmuka'];
            
            $db->beginTransaction();
            
            $txtStatus = "Rejected";
            
            $sqlQuery = $db->prepare("update t_pjum_tab set keuangan_diperiksa = (select xx.id from position_user_rpt xx where xx.user_id = :keuangan_diperiksa AND is_active = 1), status_pjum = :status_pjum where t_pjum_id = :t_pjum_id");

            $sqlQuery->bindParam(':t_pjum_id', $txtKode, PDO::PARAM_STR);
            $sqlQuery->bindParam(':keuangan_diperiksa', $keuanganDiperiksa, PDO::PARAM_STR);
            $sqlQuery->bindParam(':status_pjum', $txtStatus, PDO::PARAM_STR);

            $sqlQuery->execute();

            $db->commit();

            if($sqlQuery->rowCount() > 0){
                //header('location:permintaan_edit.php?id='.base64_encode($txtKode));

                //header('location:home.php');
                $strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
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
    
    try {
        $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
        
        $query = "select * from t_um_rpt where t_pjum_id='$txtKode'";
        $result = $db->prepare($query);
        $result->execute();

        $num = $result->rowCount();
        
        if($num > 0) {
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $txtKeperluan = $row[2];            
                $txtPeriode = $row[6]; 
                $txtLampiran = $row[5];
                $txtLampiran2 = $row[45];
                $cmbUser1 = $row[56]." - ".$row[57]." (".$row[86]." ".$row[87].")";
                $cmbUser2 = $row[58]." - ".$row[59]." (".$row[88]." ".$row[89].")";
                $cmbPengadaan1 = $row[60]." - ".$row[61]." (".$row[90]." ".$row[91].")";
                $cmbPengadaan2 = $row[62]." - ".$row[63]." (".$row[92]." ".$row[93].")";
                $txtNilaiUM = number_format($row[21],2);
                $txtEvaluasiNilaiUM = number_format($row[30],2);
                $txtNilaiPJUM = number_format($row[52],2);
                $txtSisa = number_format($row[55],2);
                $txtKelebihan = $row[85];
                $approvalStatus = $row[51];
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
    
    $str = "";
    
    try {
        $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

        $query = "select * from t_um_detail_tab where t_um_id=(select t_um_id from t_pjum_tab where t_pjum_id = '$txtKode') order by t_um_detail_id";
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
                $str = $str."<td valign='top'>".number_format($row[6],2)."</td>";
                $str = $str."<td valign='top'>".number_format($row[7],2)."</td>";
                $str = $str."<td valign='top'>".number_format($row[8],2)."</td>";
                $str = $str."<td valign='top'>".number_format($row[9],2)."</td>";
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
                    <h2 class="with-border">Persetujuan Uang Muka : <?PHP echo $txtKode;?></h2>
                    <form id="frmPermintaan" action="" method="post" enctype="multipart/form-data">
                        <div class="form-group row">
                            <label class="col-sm-2 form-control-label">Keperluan *</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><?PHP echo $txtKeperluan;?></p>
                            </div>
                            <label class="col-sm-2 form-control-label">Periode *</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><?PHP echo $txtPeriode;?></p>
                            </div>
                            <label class="col-sm-2 form-control-label">Nilai UM *</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><?PHP echo $txtNilaiUM;?></p>
                            </div>
                            <label class="col-sm-2 form-control-label">Evaluasi Nilai UM *</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><?PHP echo $txtEvaluasiNilaiUM;?></p>
                            </div>
                            <label class="col-sm-2 form-control-label">Nilai PJUM *</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><?PHP echo $txtNilaiPJUM;?></p>
                            </div>
                            <label class="col-sm-2 form-control-label">Sisa / Kurang *</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><?PHP echo $txtSisa;?></p>
                            </div>
                            <label class="col-sm-2 form-control-label">Lampiran UM</label>
                            <div class="col-sm-10">
                                <div class="form-group">
                                    <div class="input-group">
                                        <?PHP if($txtLampiran==""){echo "<span class='color-red'>Lampiran belum diupload !!</span>";}else{echo "<a href='files/$txtLampiran' target='_blank'>"."Download Lampiran</a>";}?>
                                    </div>
                                </div>
                            </div>
                            <label class="col-sm-2 form-control-label">Lampiran PJUM</label>
                            <div class="col-sm-10">
                                <div class="form-group">
                                    <div class="input-group">
                                        <?PHP if($txtLampiran2==""){echo "<span class='color-red'>Lampiran belum diupload !!</span>";}else{echo "<a href='files/$txtLampiran2' target='_blank'>"."Download Lampiran</a>";}?>
                                    </div>
                                </div>
                            </div>
                            <label class="col-sm-2 form-control-label">Bukti Transfer Pengembalian Kelebihan UM</label>
                            <div class="col-sm-10">
                                <div class="form-group">
                                    <div class="input-group">
                                        <?PHP if($txtKelebihan==""){echo "<span class='color-red'>Bukti Transfer Pengembalian Kelebihan UM belum diupload !!</span>";}else{echo "<a href='files/$txtKelebihan' target='_blank'>"."Download Bukti Transfer Pengembalian Kelebihan UM</a>";}?>
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
                                    <?PHP echo $str;?>
                                </tbody>
                            </table>
                        </div>
                
                        <h5 class="with-border">User / Pemohon</h5>
                        <div class="form-group row">
                            <label class="col-sm-2 form-control-label">Yang Meminta *</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><?PHP echo $cmbUser1;?></p>
                            </div>
                            <label class="col-sm-2 form-control-label">Disetujui *</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><?PHP echo $cmbUser2;?></p>
                            </div>
                        </div>
                
						<!--
                        <h5 class="with-border">Divisi Pengadaan</h5>
                        <div class="form-group row">
                            <label class="col-sm-2 form-control-label">Disiapkan *</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><PHP echo $cmbPengadaan1;?></p>
                            </div>
                            <label class="col-sm-2 form-control-label">Disetujui *</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><PHP echo $cmbPengadaan2;?></p>
                            </div>
                        </div>
						-->
                </div>
                <div class="box-typical box-typical-padding">
                        <div class="form-group row">
                            <?PHP
                                if($approvalStatus=="Verification"){
                                    echo "<div class='col-sm-1'>
                                            <button type='submit' class='btn btn-inline' name='btnSimpan' id='btnSimpan'>Approve</button>
                                            </div>
                                            <div class='col-sm-1'>
                                                <button type='submit' class='btn btn-inline' name='btnReject' id='btnReject'>Reject</button>
                                            </div>";
                                }else{
                                    echo "<div class='col-sm-12 alert alert-success'><strong>Anda sudah melakukan ".$approvalStatus." untuk evaluasi pertanggungjawaban uang muka ini</strong></div>";
                                }
                            ?>
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
                $('#txtTglPerolehan').daterangepicker({
                    singleDatePicker: true,
                    showDropdowns: true
                });
            });
	</script>
        
<script src="js/app.js"></script>
</body>
</html>