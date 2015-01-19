<?php

//$start = microtime(true);
// note: http://stackoverflow.com/questions/3629504/php-file-get-contents-very-slow-when-using-full-url
$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));

/* HEMICYCLE */
$rawdata = json_decode(file_get_contents($_GET['resource']));

$parties = get_parties($rawdata);

//p api
if (isset($_GET['party_set'])) {
    $chunk = '&set=' . $_GET['party_set'];
    $url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/papi/?' . http_build_query(['parties' => $parties]) . $chunk;

    $r = json_decode(file_get_contents($url,false,$context));

    $abbr2row = [];
    foreach ($r->data as $row) {
        $abbr2row[$row->abbreviation] = $row;
    }
    $parties = $r->data;
    $data = add_attributes($rawdata,$abbr2row);
} else {
    $parties = create_parties($rawdata);
    $data = $rawdata;
    $data = add_attributes($rawdata,[]);
}

usort($data, "cmp");


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
    $rows_orloj = 1; //default;


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

//sort by position
function cmp($a, $b)
{
    return $a->position - $b->position;
}

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

//create parties (for orloj later) from people
function create_parties($data) {
    $parties = [];
    foreach ($data as $key => $row) {
        if (!isset($parties[$row->party])) {
            $parties[$row->party] = new StdClass();
            $parties[$row->party]->color = $row->color;
            $parties[$row->party]->abbreviation = $row->party;
        }
    }
    return $parties;
}

//add attributes for people
function add_attributes($data,$abbr2row) {
    $option_meaning2position = [
        'against' => -1,
        'neutral' => 0,
        'for' => 1
    ];
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
        if (!isset($data[$key]->color))
            $data[$key]->color = $abbr2row[$row->party]->color;
        if (!isset($data[$key]->position)) {
            if (isset($abbr2row[$row->party]->position)) $data[$key]->position = $abbr2row[$row->party]->position*100 + $option_meaning2position[$row->option_meaning]*10000000 + $key/100;
            else $data[$key]->position = 0;
        } else {
            $data[$key]->position = (float) $data[$key]->position;
        }
        
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
