<?PHP
    session_start();

    if (!isset($_SESSION['sessunameuangmuka'])) {
        header('location:index.php');
    }

    include "koneksi/connect-db.php";
    include "f_setter_getter_serial.php";
    
    $modeDebug = 0;
    $strMessage = "";
    
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
    
    // TAMBAH
    if (isset($_POST['btnSimpan']) and $_SERVER['REQUEST_METHOD'] == "POST") {
        try {
            $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

            $txtKeperluan = $_POST['txtKeperluan'];
            $txtPeriode =$_POST['txtPeriode'];
            $txtLampiran = $_FILES['txtLampiran']['name'];
            $cmbUser1 = $_POST['cmbUser1'];
            $cmbUser2 = $_POST['cmbUser2'];
            $cmbPengadaan1 = "";
            $cmbPengadaan2 = "";
            $txtKode = getNomorBerikutnya("UM");
            $txtStatus = "Draft";
            
            if(!isset($_POST['chkTC'])){
                $chkTC = "";
            }else{
                $chkTC = $_POST['chkTC'];
            }

            if($txtKeperluan=="" || $txtPeriode=="" || $cmbUser1=="" || $cmbUser2=="" || $txtLampiran=="" || $chkTC==""){
                $strMessage = "<div class='alert alert-error'><strong>Kolom dengan tanda bintang (*) wajib diisi</strong></div>";
            }else{
                if($txtLampiran==''){
                    $db->beginTransaction();

                    $sqlQuery = $db->prepare("insert into t_um_tab(t_um_id, tgl_um, keperluan, periode, user_peminta, user_penyetuju, pengadaan_disiapkan, pengadaan_disetujui, status_um, user_pembuat, flag_tc) values(:t_um_id, now(), :keperluan, :periode, :user_peminta, :user_penyetuju, :pengadaan_disiapkan, :pengadaan_disetujui, :status_um, :user_pembuat, :flag_tc)");

                    $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':keperluan', $txtKeperluan, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':periode', $txtPeriode, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':user_peminta', $cmbUser1, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':user_penyetuju', $cmbUser2, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':pengadaan_disiapkan', $cmbPengadaan1, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':pengadaan_disetujui', $cmbPengadaan2, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':status_um', $txtStatus, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':user_pembuat', $_SESSION['sessiduangmuka'], PDO::PARAM_STR);
                    $sqlQuery->bindParam(':flag_tc', $chkTC, PDO::PARAM_STR);

                    $sqlQuery->execute();

                    $db->commit();

                    if($sqlQuery->rowCount() > 0){
                        setNomorBerikutnya("UM");

                        header('location:permintaan_edit.php?id='.base64_encode($txtKode));

                        //header('location:home.php');
                        //$strMessage = "<div class='alert alert-success'><strong>Data berhasil disimpan</strong></div>";
                    }else{
                        $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
                    }
                }else{
                    $path = "files/";

                    $name = $_FILES['txtLampiran']['name'];
                    $size = $_FILES['txtLampiran']['size'];
                    
                    if (strlen($name)) {
                        //if($size > 250000){
                        //    $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Ukuran KTP terlalu besar !!</div>";
                        //}else{
                            //list($txt, $ext) = explode(".", $name);

                            //$actual_image_name = "KTP_".$kdPelamar.".".strtolower($ext);

                            $actual_image_name = $txtKode."_".$name;
                            $tmp = $_FILES['txtLampiran']['tmp_name'];

                            if(move_uploaded_file($tmp, $path . $actual_image_name)){
                                $db->beginTransaction();

                                $sqlQuery = $db->prepare("insert into t_um_tab(t_um_id, tgl_um, keperluan, periode, user_peminta, user_penyetuju, pengadaan_disiapkan, pengadaan_disetujui, status_um, lampiran_um, user_pembuat, flag_tc) values(:t_um_id, now(), :keperluan, :periode, :user_peminta, :user_penyetuju, :pengadaan_disiapkan, :pengadaan_disetujui, :status_um, :lampiran_um, :user_pembuat, :flag_tc)");

                                $sqlQuery->bindParam(':t_um_id', $txtKode, PDO::PARAM_STR);
                                $sqlQuery->bindParam(':keperluan', $txtKeperluan, PDO::PARAM_STR);
                                $sqlQuery->bindParam(':periode', $txtPeriode, PDO::PARAM_STR);
                                $sqlQuery->bindParam(':user_peminta', $cmbUser1, PDO::PARAM_STR);
                                $sqlQuery->bindParam(':user_penyetuju', $cmbUser2, PDO::PARAM_STR);
                                $sqlQuery->bindParam(':pengadaan_disiapkan', $cmbPengadaan1, PDO::PARAM_STR);
                                $sqlQuery->bindParam(':pengadaan_disetujui', $cmbPengadaan2, PDO::PARAM_STR);
                                $sqlQuery->bindParam(':status_um', $txtStatus, PDO::PARAM_STR);
                                $sqlQuery->bindParam(':lampiran_um', $actual_image_name, PDO::PARAM_STR);
                                $sqlQuery->bindParam(':user_pembuat', $_SESSION['sessiduangmuka'], PDO::PARAM_STR);
                                $sqlQuery->bindParam(':flag_tc', $chkTC, PDO::PARAM_STR);

                                $sqlQuery->execute();

                                $db->commit();

                                if($sqlQuery->rowCount() > 0){
                                    setNomorBerikutnya("UM");

                                    header('location:permintaan_edit.php?id='.base64_encode($txtKode));
                                }else{
                                    $strMessage = "<div class='alert alert-error'><strong>Data gagal disimpan</strong></div>";
                                }
                            }else{
                                $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'>Upload lampiran gagal. Ulangi proses upload lampiran !!</div>";
                            }
                        //}
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
    }
    
    $strCmbTTD = "";
    
    try {
        $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
        
        $query = "select * from position_user_rpt WHERE position_level >= 4 and is_active = 1 order by position_level";
        $result = $db->prepare($query);
        $result->execute();

        $num = $result->rowCount();
        
        if($num > 0) {
            $strCmbTTD = $strCmbTTD."<option></option>";
            
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $strCmbTTD = $strCmbTTD."<option value='$row[0]'>$row[1] - $row[2]</option>";
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
            <?PHP echo $strLock;?>
            <div class="container-fluid">
                <div class="box-typical box-typical-padding">
                    <h2 class="with-border">Permintaan Uang Muka</h2>
                    <form id="frmPermintaan" action="" method="post" enctype="multipart/form-data">
                        <div class="form-group row">
                            <label class="col-sm-2 form-control-label">Keperluan *</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><textarea rows="3" class="form-control" name="txtKeperluan" id="txtKeperluan"></textarea></p>
                            </div>
                            <label class="col-sm-2 form-control-label">Periode *</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><input type="text" class="form-control" name="txtPeriode" id="txtPeriode"></p>
                            </div>
                            <label class="col-sm-2 form-control-label">Lampiran *</label>
                            <div class="col-sm-10">
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="file" class="form-control" name="txtLampiran" id="txtLampiran">
                                    </div>
                                </div>
                            </div>
                        </div>

                    <h5 class="with-border">User / Pemohon</h5>
                        <div class="form-group row">
                            <label class="col-sm-2 form-control-label">Yang Meminta *</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><select class="select2" name="cmbUser1" id="cmbUser1"><?PHP echo $strCmbTTD;?>
                                </select></p>
                            </div>
                            <label class="col-sm-2 form-control-label">Disetujui *</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><select class="select2" name="cmbUser2" id="cmbUser2"><?PHP echo $strCmbTTD;?>
                                </select></p>
                            </div>
                        </div>

                    <!--<h5 class="with-border">Divisi Pengadaan</h5>
                        <div class="form-group row">
                            <label class="col-sm-2 form-control-label">Disiapkan *</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><select class="select2" name="cmbPengadaan1" id="cmbPengadaan1"><PHP //echo $strCmbTTD;?>
                                </select></p>
                            </div>
                            <label class="col-sm-2 form-control-label">Disetujui *</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><select class="select2" name="cmbPengadaan2" id="cmbPengadaan2"><PHP //echo $strCmbTTD;?>
                                </select></p>
                            </div>
                        </div>-->
                </div>
                <div class="box-typical box-typical-padding">
                        <div class="form-group row">
                            <label class="col-sm-12 form-control-label"><div class="alert alert-blue-dirty">Sesuai  Prosedur  Pengajuan  dan  Pertanggungjawaban  Uang  Muka,  kami  selaku  User/Pemohon  Uang  Muka  dengan  ini  menyatakan  bahwa  jika  dalam  jangka  waktu  30  hari  setelah  tanggal  penerimaan  uang  muka  kami  belum  mempertanggungjawabkan  uang  muka  tersebut,  kami  bersedia  dikenakan  sanksi  administrasi  berupa:<br>1.  Pemblokiran  Sistem  Uang  Muka  Online  sampai  uang  muka  tersebut  dipertanggungjawabkan  atau  dilakukan  perpanjangan  pertanggungjawaban  Uang  Muka  dengan  jangka  waktu  perpanjangan  maksimal  14 hari kalender.<br>2. Penahanan tunjangan jika yang bersangkutan tidak dapat memenuhi kewajiban Perpanjangan Pertanggungjawaban Uang Muka sampai dengan Uang Muka tersebut dipertanggungjawabkan.<br></div></label>
                            <div class="col-sm-12">
                                <p class="form-control-static">
                                   <input type="checkbox" name="chkTC" id="chkTC" value="1"> <b>Saya setuju dengan sanksi administrasi sesuai prosedur  "Pengajuan  dan  Pertanggungjawaban  Uang  Muka" (wajib dicentang)</b>
                                </p>
                            </div>
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
                $('#txtTglPerolehan').daterangepicker({
                    singleDatePicker: true,
                    showDropdowns: true
                });
            });
	</script>
        
<script src="js/app.js"></script>
</body>
</html>