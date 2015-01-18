<?php

//$start = microtime(true);
// note: http://stackoverflow.com/questions/3629504/php-file-get-contents-very-slow-when-using-full-url
$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));

/* HEMICYCLE */
$rawdata = json_decode(file_get_contents($_GET['resource']));

$parties = get_parties($rawdata);

//p api
if (isset($_GET['party_set']))
    $chunk = '&set=' . $_GET['party_set'];
else
    $chunk = '';
$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/papi/?' . http_build_query(['parties' => $parties]) . $chunk;

$r = json_decode(file_get_contents($url,false,$context));

$abbr2color = [];
foreach ($r->data as $row) {
    $abbr2color[$row->abbreviation] = $row->color;
}
$parties = $r->data;

$data = add_attributes($rawdata,$abbr2color);

//n api
$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/napi/?n=' . count($data);
$r = json_decode(file_get_contents($url,false,$context));
$dat = $r->data;

//arcs
$arcs = create_arcs($data);

/* LEGEND */
//language / legend
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $chunk = '&lang=' . $_GET['lang'];
} else {
    $chunk = '';
    $lang = 'en';
}
$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/lapi/?' . $chunk;
$r = json_decode(file_get_contents($url,false,$context));

$against = new StdClass();
$against->color = 'red';
$against->text = $r->data->against;
$against->class = 'legend-option-against';
$for = new StdClass();
$for->color = 'green';
$for->text = $r->data->for;
$for->class = 'legend-option-for';

$legend = [
    'label' => [$r->data->legend],
    'icons' => [$against,$for]
];


/* ORLOJ */
// parties for orloj
$orloj_parties = [];
foreach ($parties as $party) {
    $orloj_parties[] = ['color'=>$party->color, 'text' => $party->abbreviation];
}

//width
if (isset($_GET['width']))
    $width = $_GET['width'];
else
    $width = 400; //default;

if (isset($_GET['orloj_rows']))
    $rows_orloj = $_GET['orloj_rows'];
else
    $rows_orloj = 400; //default;


/* TEMPLATE */
// template
$html = file_get_contents('widget.tpl');
$replace = [
  '{_DATA}' => $data,
  '{_DAT}' => $dat,
  '{_ARCS}' => $arcs,
  '{_LEGEND}' => $legend,
  '{_ORLOJ_PARTIES}' => $orloj_parties,
  '{_WIDTH}' => $width,
  '{_ROWS_ORLOJ}' => $rows_orloj,
  '{_LANG}' => $lang
];
foreach ($replace as $k => $r)
    $html = str_replace($k,json_encode($r),$html);
    
echo $html;



//$t = (microtime(true) - $start);
//echo "time: " . $t . "<br>\n";

/* FUNCTIONS */

//select distinct parties
function get_parties ($data) {
    $list = [];
    foreach ($data as $row) {
        $list[$row->party] = $row->party;
    }
    $out = [];
    foreach ($list as $item)
        $out[] = $item;
    return $out;
}

//add attributes for people
function add_attributes($data,$abbr2color) {
    foreach ($data as $key => $row) {
        if (in_array($row->option_meaning,['for','against'])) {
            $data[$key]->badge_opacity = 1;
            $data[$key]->opacity = 1;
        } else {
            $data[$key]->badge_opacity = 0;
            $data[$key]->opacity = 0.15;
        }
        if ($row->option_meaning == 'for')
            $data[$key]->badge_color = 'green';
        if ($row->option_meaning == 'against')
            $data[$key]->badge_color = 'red';
        $data[$key]->color = $abbr2color[$row->party];
    }
    return $data;
}

function create_arcs($data) {
    $limits = [
        'for'=>['lo'=>null,'hi'=>null,'color'=>'green'],
        'against'=>['lo'=>null,'hi'=>null,'color'=>'darkred'],
    ];
    foreach($data as $key=>$row) {
        if (isset($limits[$row->option_meaning])){
            if (is_null($limits[$row->option_meaning]['lo']))
                $limits[$row->option_meaning]['lo'] = $key;
            $limits[$row->option_meaning]['hi'] = $key;
        }
    }
    $arcs = [];
    foreach ($limits as $limit) {
        if (!is_null($limit['lo'])) {
            $a = new StdClass();
            $a->start = $limit['lo'];
            $a->end = $limit['hi'];
            $a->color = $limit['color'];
            $a->opacity = 0.15;
            $arcs[] = $a;
        }
    }
    return $arcs;
}

?>
