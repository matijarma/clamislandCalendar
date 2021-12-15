<?php 
// Complete ICS function from https://gist.github.com/jakebellacera/635416#file-ics-php
include('ICS.php');

date_default_timezone_set('UTC');
//The public google sheet that is used as a database
$nekaj = google_sheet("https://docs.google.com/spreadsheets/d/1gpGBE_i1TmBMZ0x_2LDMTw0uxUjQrKwbQCqYYkSukIg/edit?usp=sharing");

//load all rows into an array
$i=0;
foreach ($nekaj as $redovi){
	$sve[$i] = $redovi;
	$i++;
}
//reduce total by 1 to eliminate the header row
$ukupno = $i-1;

//extract, sort and format the values
for($i=1;$i<=$ukupno;$i++){
	$pearltraits[$i] = addslashes(addcslashes($sve[$i]['B']));
	$s = " 01:00";
	if($sve[$i]['D'] == "13:00 (1 p.m.)")
		$s = " 13:00";
	
	$s = strtotime($sve[$i]['C'] . $s);
	
	$start[$i] = makese($s,1);
	$end[$i] = makese($s,2);
}

//create pearl trait periods as events
for($i=1;$i<=$ukupno;$i++){
	$ics[$i] = new ICS(array(
	  'location' => "https://clamisland.fi/bank",
	  'description' => "$pearltraits[$i]",
	  'dtstart' => "$start[$i]",
	  'dtend' => "$end[$i]",
	  'summary' => "$pearltraits[$i]",
	  'url' => "https://clamisland.fi"
	));
}

//generate the ICS file
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename=invite.ics');
for($i=1;$i<=$ukupno;$i++){
	echo $ics[$i]->to_string();
	echo "\n";
}

//pearl traits period start and end
function makese($pocetak,$out){
	
	$kraj = $pocetak + (12*60*60);
	$pocetak = date(DATE_ATOM,$pocetak);
	$kraj = date(DATE_ATOM,$kraj);
	
	if($out==1)
		return $pocetak;
	if($out==2)
		return $kraj;
}
//simple get-all-rows-from-google-sheet function
function google_sheet($url = NULL) {
 
    $array = array();
 
    if ($url):
       // initialize curl
       $curl = curl_init();
       curl_setopt($curl, CURLOPT_URL, $url);
       curl_setopt($curl, CURLOPT_HEADER, 0);
       curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
 
       // get the spreadsheet data using curl
       $sheet = curl_exec($curl);
       curl_close($curl);
 
       // find the table pattern and return the mark-up
       preg_match('/(<table[^>]+>)(.+)(<\/table>)/', $sheet, $matches);
       $data = $matches['0'];
 
       // convert the HTML (XML) mark-up to JSON
       $cells_xml = new SimpleXMLElement($data);
       $cells_json = json_encode($cells_xml);
       $cells = json_decode($cells_json, TRUE);
    endif;
 
    // Convert the JSON array to an array of just the table data
    // This will strip out any Google Sheets formatting and identifiers if they exist
    if ( is_array($cells) ):
       foreach ($cells['tbody']['tr'] as $row => $row_data):
          $column = 'A';
          foreach ($row_data['td'] as $column_index => $cell):
             // Check that the cell is populated and get the value.
             if (!is_array($cell)):
                $array[($row + 1)][$column++] = $cell;
             elseif ($cell['div']):
                $array[($row + 1)][$column++] = $cell['div'];
             endif;
          endforeach;
       endforeach;
    endif;
 
    return $array;
 }
 
?>