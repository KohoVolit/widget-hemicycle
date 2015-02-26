<?php
/**
* prepares data for visualizing a single vote event
* requires valid:
* country_code  e.g., 'cz'
* code  parliament code, e.g., 'psp'
* vote_event_id    id of vote event
* party which type of group classification shall be used for party; e.g., 'parliamentary_group'
*/

// there are problems with file_get_contents and encoding complex requests as where={"$not":{ ...
// therefore using Requests
include("../examples/Requests.php");
Requests::register_autoloader();

$api = "http://api.parldata.eu";
$url = implode('/',[$api,$_GET['country_code'],$_GET['code']]) . '/';


// get the info about vote event (e.g., date)
$resource = 'vote-events?where={"id":"'.$_GET['vote_event_id'].'"}';
$vote_events = get_all_pages($url,$resource);

// get all votes from the vote event
$resource = 'votes?where={"vote_event_id":"'.$_GET['vote_event_id'].'"}';
$embed = '&embed=["voter","group"]';
            
//because of this bug: https://github.com/nicolaiarocci/eve/issues/494
//we cannot use: $resource = 'votes?where={"vote_event_id":"'.$_GET['vote_event_id'].'"}&embed=["voter"]';

$votes = get_all_pages($url,$resource,$embed);

$out = [];
foreach ($votes as $vote) {
    $o = [];
    if (isset($vote->voter->name))
        $o['name'] = $vote->voter->name;
    else {
        if (isset($vote->voter->family_name))
            $o['name'] = $vote->voter->family_name;
        else
            $o['name'] = $vote->voter->id;
    }
    $o['option_meaning'] = option2meaning($vote->option);
    $o['id'] = $vote->voter->id;
    // get the right party
    // if the group is in votes, it is easy
    if (isset($vote->group)) {
        $o['party'] = $vote->group->name;
    } else {
        // trying get it from memberships
        $resource = 'memberships?' . 
            'where={"person_id":"'.$vote->voter->id . '",'.
            '"start_date": {"$not": {"$gt": "'.str_replace('T', ' ', $vote_events[0]->start_date).'"}},'.
            '"end_date": {"$not": {"$lt": "'.str_replace('T', ' ', $vote_events[0]->start_date).'"}}}';
        $embed = '&embed=["organization"]';
       
        $membership = get_all_pages($url,$resource,$embed);
        
        if (isset($membership->organization) and isset($membership->organization->classification)) {
            if (($membership->organization->classification == $_GET['classification']) and (property_exists($membership,"start_date"))) {  //fix with start date
               $o['party'] = html_entity_decode($membership->organization->name);
            } else {
                $o['party'] = '-';
            }
        } else {
            $o['party'] = '-';
        }

    }
    
    $out[] = $o;
}


// Set HTTP Response Content Type
header('Content-Type: application/json; charset=utf-8');

// Format data into a JSON response
echo json_encode($out);

function option2meaning($o) {
    if ($o == 'yes')
        return "for";
    if ($o == 'no')
        return "against";
    if ($o == 'abstain')
        return "against";
    if ($o == 'absent')
        return "neutral";
    if ($o == 'not voting')
        return "against";
    return "neutral";
}

function get_all_pages($url,$resource,$embed = '') {
    //echo $url.$resource;
    $response = Requests::get($url.$resource.$embed);
    $page = json_decode(html_entity_decode($response->body));
    if (isset($page->_items))
        $items = $page->_items;
    else
        $items = [];
    while(isset($page->_links->next)) {
        $resource = $page->_links->next->href;
        //echo $url.$resource;
        $response = Requests::get($url.$resource.$embed);
        $page = json_decode($response->body);
        $items = array_merge($items,$page->_items);
    }
    return $items;
}

?>
