<?php

$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));

$data = csv2array(file_get_contents($_GET['url'],false,$context));

// Set HTTP Response Content Type
header('Content-Type: application/json; charset=utf-8');

// Format data into a JSON response
echo json_encode($data);

//first row contains the names of attributes
function csv2array($data) {

    $rows = explode("\n",$data);
    $rows0 = str_getcsv($rows[0]);
    
    $out = [];
    foreach($rows as $key => $r) {
        if ($key > 0) {
            $row = str_getcsv($r);
            $oitem = [];
            foreach ($row as $ikey => $item) {
                $oitem[$rows0[$ikey]] = $item;
            }
            if (count($oitem) > 1)
                $out[] = $oitem;
        }
    }
    return $out;
}

?>
