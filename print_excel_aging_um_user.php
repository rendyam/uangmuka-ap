<?php
    session_start();

    if(!isset($_SESSION['sessunameuangmuka'])){
        header('location:index.php');
    }
    
    error_reporting(E_ALL);
    require_once 'PHPExcel/Classes/PHPExcel.php';

    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();
    
    $rowNya = 1;
    
    // Add some data
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.$rowNya, "No")
            ->setCellValue('B'.$rowNya, "Kode")
            ->setCellValue('C'.$rowNya, "Tgl UM")
            ->setCellValue('D'.$rowNya, "Divisi")
            ->setCellValue('E'.$rowNya, "Keperluan")
            ->setCellValue('F'.$rowNya, "Status")
            ->setCellValue('G'.$rowNya, "Diajukan")
            ->setCellValue('H'.$rowNya, "Dievaluasi")
            ->setCellValue('I'.$rowNya, "Tgl Diterima")
            ->setCellValue('J'.$rowNya, "User Peminta")
            ->setCellValue('K'.$rowNya, "Tgl User Peminta")
            ->setCellValue('L'.$rowNya, "User Disetujui")
            ->setCellValue('M'.$rowNya, "Tgl User Disetujui")
            ->setCellValue('N'.$rowNya, "Pengadaan Disiapkan")
            ->setCellValue('O'.$rowNya, "Tgl Pengadaan Disiapkan")
            ->setCellValue('P'.$rowNya, "Pengadaan Disetujui")
            ->setCellValue('Q'.$rowNya, "Tgl Pengadaan Disetujui")
            ->setCellValue('R'.$rowNya, "Keuangan Diperiksa")
            ->setCellValue('S'.$rowNya, "Keuangan Disiapkan")
            ->setCellValue('T'.$rowNya, "Tgl Keuangan Disiapkan")
            ->setCellValue('U'.$rowNya, "Keuangan Disetujui")
            ->setCellValue('V'.$rowNya, "Tgl Keuangan Disetujui")
            ->setCellValue('W'.$rowNya, "Aging")
    ;
    
    $rowNya = $rowNya + 1;
    
    try {
        include "koneksi/connect-db.php";
        
        $db = new PDO("mysql:host=$host;port=$dbport;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
        
        //$query = "SELECT * FROM inovasi_rpt order by tanggal";
        
        if($_SESSION['sessevaluangmuka']==1 || $_SESSION['sesspencairanuangmuka']==1 || $_SESSION['sessuangmukaro']==1){
            $query = "SELECT a.t_um_id, a.tgl_um, divisions.NAME AS nama_divisi, a.keperluan, a.status_um, a.nilai_um, a.evaluasi_nilai_um, a.tgl_diterima, a.nama_peminta, a.user_peminta_tgl, a.nama_penyetuju, a.user_penyetuju_tgl, a.nama_pengadaan_disiapkan, a.user_pengadaan_disiapkan_tgl, a.nama_pengadaan_disetujui, a.user_pengadaan_disetujui_tgl, a.nama_keuangan_diperiksa, a.nama_keuangan_penyetuju_1, a.user_keuangan_penyetuju_1_tgl, a.nama_keuangan_penyetuju_2, a.user_keuangan_penyetuju_2_tgl, aging FROM t_um_rpt a LEFT JOIN divisions ON a.divisi=divisions.id where status_um = 'Received by User' and (status_pjum <> 'Approved' or status_pjum is null) order by aging desc";
        }
        
        $result = $db->prepare($query);
        $result->execute();     

        $num = $result->rowCount();

        $no = 0;
        
        if($num > 0){
            while($row = $result->fetch(PDO::FETCH_NUM)){
                $no = $no + 1;

                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue("A$rowNya", $no)
                        ->setCellValue("B$rowNya", $row[0])
                        ->setCellValue("C$rowNya", $row[1])
                        ->setCellValue("D$rowNya", $row[2])
                        ->setCellValue("E$rowNya", $row[3])
                        ->setCellValue("F$rowNya", $row[4])
                        ->setCellValue("G$rowNya", $row[5])
                        ->setCellValue("H$rowNya", $row[6])
                        ->setCellValue("I$rowNya", $row[7])
                        ->setCellValue("J$rowNya", $row[8])
                        ->setCellValue("K$rowNya", $row[9])
                        ->setCellValue("L$rowNya", $row[10])
                        ->setCellValue("M$rowNya", $row[11])
                        ->setCellValue("N$rowNya", $row[12])
                        ->setCellValue("O$rowNya", $row[13])
                        ->setCellValue("P$rowNya", $row[14])
                        ->setCellValue("Q$rowNya", $row[15])
                        ->setCellValue("R$rowNya", $row[16])
                        ->setCellValue("S$rowNya", $row[17])
                        ->setCellValue("T$rowNya", $row[18])
                        ->setCellValue("U$rowNya", $row[19])
                        ->setCellValue("V$rowNya", $row[20])
                        ->setCellValue("W$rowNya", $row[21])
                ;
                
                $rowNya = $rowNya + 1;
            }
        }
        
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setAutoSize(true);

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('AGING UM');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        $query = "SELECT SYSDATE() as dt";

        $result = $db->prepare($query);
        $result->execute();

        $num = $result->rowCount();

        if($num > 0) {
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $tgl = "AGING UM ".$row[0];
            }
        }

        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$tgl.'.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    } catch (Exception $e) {
        echo $e->getMessage();
    }

?>