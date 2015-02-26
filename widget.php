<?php

//$start = microtime(true);
//error_reporting(0);

//cache
if ((!isset($_GET['nocache'])) or (!$_GET['nocache'])) {
    $file_name = 'cache/html/' . md5(curPageURL());
    $time  = time();
    if (is_readable($file_name) and ($time - filemtime($file_name) < 60*60*24*365)) // 1 year
        $cache = true;
    else 
        $cache = false;
} else
    $cache = false;

if ($cache) {
    $html = file_get_contents('cache/html/' . md5(curPageURL()));
    echo $html;
} else {

    // note: http://stackoverflow.com/questions/3629504/php-file-get-contents-very-slow-when-using-full-url
    $context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));

    /* HEMICYCLE */
    $rawdata = json_decode(file_get_contents($_GET['resource'],false,$context));

    $parties = get_parties($rawdata);

    //p api
    if (isset($_GET['party_set']) and ($_GET['party_set'] != '')) {
        $chunk = '&set=' . $_GET['party_set'];
        $url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/papi/?' . http_build_query(['parties' => $parties]) . $chunk;

        $r = json_decode(file_get_contents($url,false,$context));
        
        //print_r($r);die();

        $abbr2row = [];
        foreach ($r->data as $row) {
            $abbr2row[$row->key] = $row;
        }
        $parties = $r->data;
        $data = add_attributes($rawdata,$abbr2row);
    } else {
        $parties = create_parties($rawdata);
        $data = $rawdata;
        $data = add_attributes($rawdata,[]);
    }
    usort($data, "cmp");
    
    foreach($data as $key=>$sen) {
        if (isset($abbr2row[$sen->party])) {
            $row = $abbr2row[$sen->party];
            if (isset($row->other_names)) {
                foreach ($row->other_names as $name) {
                    if(isset($name->note) and ($name->note == "abbreviation"))
                        $data[$key]->party = $name->name;
                }
            }
        }
    }


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
        if (isset($party->other_names)) {
            foreach ($party->other_names as $name) {
                if(isset($name->note) and ($name->note == "abbreviation"))
                    $party->abbreviation = $name->name;
            }
        }
        if (!isset($party->other_names)) {
            $party->abbreviation = $party->name;
        }
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

    //og:image (generated png)
    $og_image = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/cache/png/' . md5(curPageURL()) . ".png";
    //og:url
    $og_url = curPageURL();
    
    //format
    if (isset($_GET['format']))
        $format = $_GET['format'];
    else
        $format = 'html'; //default;    

    /* TEMPLATE */
    // template
    $html = file_get_contents('widget.tpl');
    $replace_jsonized = [
      '_DATA' => $data,
      '_DAT' => $dat,
      '_ARCS' => $arcs,
      '_LEGEND' => $legend,
      '_ORLOJ_PARTIES' => $orloj_parties,
      '_WIDTH' => $width,
      '_ROWS_ORLOJ' => $rows_orloj,
    ];
    foreach ($replace_jsonized as $k => $r)
        $html = str_replace('{{'.$k.'}}',json_encode($r),$html);

    $replace = [
      '_LANG' => $lang,
      '_OG_IMAGE' => $og_image,
      '_OG_URL' => $og_url,
      '_FORMAT' => $format,
    ];
    foreach ($replace as $k => $r)
        $html = str_replace('{{'.$k.'}}',$r,$html);
       
    echo $html;

    //write to cache
    file_put_contents('cache/html/' . md5(curPageURL()),$html);

}

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
            if (isset($row->color))
                $parties[$row->party]->color = $row->color;
            else
                $parties[$row->party]->color = 'gray';
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
        if (!isset($data[$key]->color)) {
            if (isset($abbr2row[$row->party]))
                $data[$key]->color = $abbr2row[$row->party]->color;
            else
                $data[$key]->color = 'gray';
        }
        if (!isset($data[$key]->position)) {
            if (isset($abbr2row[$row->party]->position)) 
                $data[$key]->position = $abbr2row[$row->party]->position*100 + $option_meaning2position[$row->option_meaning]*10000000 + $key/100;
            else 
                $data[$key]->position = 0;
        } else {
            $data[$key]->position = (float) $data[$key]->position*100 + $option_meaning2position[$row->option_meaning]*10000000 + $key/100;
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

function curPageURL() {
 $pageURL = $_SERVER["REQUEST_SCHEME"];
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}

?>
