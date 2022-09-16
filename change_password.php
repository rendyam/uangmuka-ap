<?PHP
    session_start();

    if (!isset($_SESSION['sessionuseridkm'])) {
        header('location:index.php');
    }

    include "koneksi/connect-db.php";
    
    $modeDebug = 0;
    
    if (isset($_POST['btnSimpan']) and $_SERVER['REQUEST_METHOD'] == "POST") {
       $strMessage = "";
    
        try {
            $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
            
            $oldPassword = $_POST["txtPasswordLama"];
            $newPassword = $_POST["txtPasswordLama2"];
            $newPassword2 = $_POST["txtPasswordBaru"];

            if($oldPassword=="" || $newPassword=="" || $newPassword2==""){
                echo "tidaklengkap";
            }else{
                if($newPassword==$newPassword2){
                    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    $sqlQuery = $db->prepare("select user_id from user_tab where user_id = :username and user_password = :password");

                    $username = $_SESSION['sessionuseridkm'];
                    $password = md5($oldPassword);

                    $sqlQuery->bindParam(':username', $username, PDO::PARAM_STR);
                    $sqlQuery->bindParam(':password', $password, PDO::PARAM_STR);

                    $sqlQuery->execute();

                    $rowCount = $sqlQuery->rowCount();

                    if($rowCount > 0){
                        $db->beginTransaction();

                        $sqlQuery = $db->prepare("update user_tab set user_password = :password where user_id = :username");

                        $username = $_SESSION['sessionuseridkm'];
                        $password = md5($newPassword);

                        $sqlQuery->bindParam(':username', $username, PDO::PARAM_STR);
                        $sqlQuery->bindParam(':password', $password, PDO::PARAM_STR);

                        $sqlQuery->execute();

                        $db->commit();

                        if($sqlQuery->rowCount() > 0){
                            $strMessage = "<div class='alert alert-info alert-fill alert-close alert-dismissible fade show' role='alert'><center>Password berhasil dirubah</center></div>";
                        }else{
                            $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'><center>Password gagal dirubah</center></div>";
                        }
                    }else{
                        $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'><center>Password lama salah</center></div>";
                    }
                }else{
                    $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'><center>Password lama tidak sama</center></div>";
                }
            }
            
            $db = null;
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
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<title>Knowledge Management PT. KBS</title>

	<link href="img/favicon.144x144.png" rel="apple-touch-icon" type="image/png" sizes="144x144">
	<link href="img/favicon.114x114.png" rel="apple-touch-icon" type="image/png" sizes="114x114">
	<link href="img/favicon.72x72.png" rel="apple-touch-icon" type="image/png" sizes="72x72">
	<link href="img/favicon.57x57.png" rel="apple-touch-icon" type="image/png">
	<link href="img/favicon.png" rel="icon" type="image/png">
	<link href="img/favicon.ico" rel="shortcut icon">

	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
    <link rel="stylesheet" href="css/separate/vendor/slick.min.css">
    <link rel="stylesheet" href="css/separate/vendor/select2.min.css">
    <link rel="stylesheet" href="css/separate/pages/widgets.min.css">
    <link rel="stylesheet" href="css/separate/pages/profile-2.min.css">
    <link rel="stylesheet" href="css/lib/font-awesome/font-awesome.min.css">
    <link rel="stylesheet" href="css/lib/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body class="with-side-menu">

	<header class="site-header">
	    <div class="container-fluid">
                
                <!logo startui-->
	        <a href="#" class="site-logo">
	            <img class="hidden-md-down" src="img/logo-2.png" alt="">
	            <img class="hidden-lg-down" src="img/logo-2-mob.png" alt="">
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
            <div class="container-fluid">
                <div class="box-typical box-typical-padding">
                    <?PHP echo $strMessage;?>
                    <h2 class="with-border">Change Password</h2>
                    <form id="frmChangePassword" action="" method="post" enctype="multipart/form-data">
                        <div class="form-group row">
                            <label class="col-sm-2 form-control-label">Password Lama</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><input type="password" class="form-control" name="txtPasswordLama" id="txtPasswordLama"></p>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 form-control-label">Password Baru</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><input type="password" class="form-control" name="txtPasswordLama2" id="txtPasswordLama2"></p>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 form-control-label">Ulangi Password Baru</label>
                            <div class="col-sm-10">
                                <p class="form-control-static"><input type="password" class="form-control" name="txtPasswordBaru" id="txtPasswordBaru"></p>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <p class="form-control-static"><button type="submit" class="btn btn-inline" name="btnSimpan" id="btnSimpan">Save</button></p>
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
        
<script src="js/app.js"></script>
</body>
</html>