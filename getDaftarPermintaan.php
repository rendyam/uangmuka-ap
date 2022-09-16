<?PHP
    // SQL server connection information
    $sql_details = array(
        'user' => 'root',
        'pass' => '',
        'db'   => 'db_efile',
        'host' => 'localhost'
    );
    
    // DB table to use
    $table = 't_um_tab';
    
    // Table's primary key
    $primaryKey = 't_um_id';
    
    // Array of database columns which should be read and sent back to DataTables.
    // The `db` parameter represents the column name in the database, while the `dt`
    // parameter represents the DataTables column identifier. In this case simple
    // indexes
    $columns = array(
        array( 'db' => 't_um_id', 'dt' => 0 ),
        array( 'db' => 'tgl_um', 'dt' => 1 ),
        array( 'db' => 'keperluan', 'dt' => 2 ),
        array( 'db' => 'status_um', 'dt' => 3 ),
        array(
            'db'        => 't_um_id',
            'dt'        => 4,
            'formatter' => function( $d, $row ) {
                return "<a class='btn btn-success' href='permintaan_edit.php?id=".base64_encode($row[0])."'><i class='icon-edit icon-white'></i> Edit</a>";
            }
        )
    );
    
    require( 'ssp.class.php' );
 
    echo json_encode(
        SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
    );

?>