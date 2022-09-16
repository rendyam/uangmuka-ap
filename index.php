<?php
    session_start();
    
    if(isset($_SESSION['sessunameuangmuka'])){
        header('location:home.php');
    }else{
        header('location:'.$live_server.'/efile');
    }
    
    include "koneksi/connect-db.php";
    
    $modeDebug = 0;
    $strMessage = "";
    
    if (isset($_POST['btnSimpan']) and $_SERVER['REQUEST_METHOD'] == "POST") {
        $passDB = "";

        try {
            $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sqlQuery = $db->prepare("select a.id, a.name, a.email, a.password, a.division_id, a.um_eval, a.um_pencairan from users a where a.nik = :username");

            $username = $_POST["txtUser"];
            $password = $_POST["txtPassword"];

            $sqlQuery->bindParam(':username', $username, PDO::PARAM_STR);

            $sqlQuery->execute();

            $result = $sqlQuery->fetchAll();

            $rowCount = $sqlQuery->rowCount();

            if($rowCount > 0){
                foreach($result as $row){
                    $passDB = $row[3];
                        
                    if(password_verify($password, $passDB)){
                        $_SESSION['sessiduangmuka'] = $row[0];
                        $_SESSION['sessunameuangmuka'] = $row[2];
                        $_SESSION['sessnameuangmuka'] = $row[1];
                        $_SESSION['sessdivisiunameuangmuka'] = $row[4];
                        $_SESSION['sessevaluangmuka'] = $row[5];
                        $_SESSION['sesspencairanuangmuka'] = $row[6];

                        header('Location: home.php');
                    }else{
                        $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'><center>Login Gagal</center></div>";
                    }
                }
            }else{
                $strMessage = "<div class='alert alert-danger alert-fill alert-close alert-dismissible fade show' role='alert'><center>Login Gagal</center></div>";
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
	<title>Uang Muka PT. KBS</title>

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
    <link rel="stylesheet" href="css/separate/pages/login.min.css">
    <link rel="stylesheet" href="css/lib/font-awesome/font-awesome.min.css">
    <link rel="stylesheet" href="css/lib/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>

    <div class="page-center">
        <div class="page-center-in">
            <div class="container-fluid">
                <form id="frmLogin" class="sign-box" action="" method="post" enctype="multipart/form-data">
                    <!--<div class="sign-avatar">
                        <img src="img/logo-kms.png" alt="">
                    </div>-->
                    <header class="sign-title">Uang Muka PT. KBS</header>
                    <?PHP echo $strMessage;?>
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="Username" name="txtUser" id="txtUser"/>
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" placeholder="Password" name="txtPassword" id="txtPassword"/>
                    </div>
                    <button type="submit" class="btn btn-rounded" name="btnSimpan" id="btnSimpan">Login</button>
                    <!--<button type="button" class="close">
                        <span aria-hidden="true">&times;</span>
                    </button>-->
                </form>
            </div>
        </div>
    </div><!--.page-center-->


<script src="js/lib/jquery/jquery-3.2.1.min.js"></script>
<script src="js/lib/popper/popper.min.js"></script>
<script src="js/lib/tether/tether.min.js"></script>
<script src="js/lib/bootstrap/bootstrap.min.js"></script>
<script src="js/plugins.js"></script>
    <script type="text/javascript" src="js/lib/match-height/jquery.matchHeight.min.js"></script>
    <script>
        $(function() {
            $('.page-center').matchHeight({
                target: $('html')
            });

            $(window).resize(function(){
                setTimeout(function(){
                    $('.page-center').matchHeight({ remove: true });
                    $('.page-center').matchHeight({
                        target: $('html')
                    });
                },100);
            });
        });
    </script>
<script src="js/app.js"></script>
</body>
</html>