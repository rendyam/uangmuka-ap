<?PHP
    // GET NOMOR BERIKUTNYA CQI
    function getNomorBerikutnya($IDSerial){
        try {
            include "koneksi/connect-db.php";
            
            $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
            
            $result = $db->prepare("SELECT SERIAL_ID, PREFIX, START_VALUE, NEXT_VALUE, LENGTH FROM gen_serial_tab where SERIAL_ID='$IDSerial'");
            $result->execute();

            $num = $result->rowCount();

            if($num > 0) {
                while ($row = $result->fetch(PDO::FETCH_NUM)) {
                    $tahun = date("Y");
                    $sequence = str_pad($row[3], $row[4], '0', STR_PAD_LEFT);
                    
                    return $row[0].$tahun.$sequence;
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
    // SET NOMOR BERIKUTNYA CQI
    function setNomorBerikutnya($IDSerial){
        try {
            include "koneksi/connect-db.php";
            
            $db = new PDO("mysql:host=$host;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));

            $db->beginTransaction();

            $sqlQuery = $db->prepare("UPDATE gen_serial_tab SET NEXT_VALUE = NEXT_VALUE + 1 WHERE SERIAL_ID = :id");

            $sqlQuery->bindParam(':id', $IDSerial, PDO::PARAM_STR);

            $sqlQuery->execute();

            $db->commit();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
?>