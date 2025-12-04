<?php
global $wpdb;
// Load WordPress environment
require_once( dirname(__FILE__) . '/wp-load.php' );

if(empty($crimeData)) {
    update_option( 'crime_data_inprogress',true);
    echo get_option( 'crime_data_inprogress')." If inprogress empty";
} elseif ( $crimeData == false ) {
    update_option( 'crime_data_inprogress',true);
    echo get_option( 'crime_data_inprogress')." elseIf inprogress empty";
} else {
    echo get_option( 'crime_data_inprogress')." Process already inprogress";
    exit;
}

die();
// Table name
$tablename = $wpdb->prefix."crimedata";
$totalInserted = 0;
$totalRowCsv=0;

$time_start = microtime(true);
$row = 1;
$errorCount = 0;
$errorContainer = array();
if (($handle = fopen("crimedata_010123_to_082824.csv", "r")) !== FALSE) {
  while (($csvData = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $dataLen = count($csvData);
    //echo "<p> $dataLen fields in line $row: <br /></p>\n";
    $row++;
        // Assign value to variables
        $caseno = trim($csvData[0]);
        $primary_id = trim($csvData[1]);
        // $crime_date = trim($csvData[2]);
        $crime_date_raw = DateTime::createFromFormat('m/d/Y', trim($csvData[2]));
        if ($crime_date_raw) {
          $crime_date = $crime_date_raw->format('Y-m-d'); // Store as YYYY-MM-DD
        } 
        $crime_time = substr(trim($csvData[3]),0,8);
        $address = trim($csvData[4]);
        $crime_type = trim($csvData[5]);
        $longitude = (float)trim($csvData[6]);
        $latitude = (float)trim($csvData[7]);
        $latLng='lat,'.$latitude.'|lng,'.$longitude;
        $crime_category = trim($csvData[8]);
        $police_district = trim($csvData[9]);
        $psa = trim($csvData[10]);
        
        $type = trim($csvData[3]);
        // $pdate= date("Y-m-d");
        // $upDate= date("Y-m-d");
        $current_time = current_time('mysql');
        $noterrorFound= true;
        // Validation Check

        if(empty($caseno)){
          $emptyCaseNo[]= $totalRowCsv+2;
          $noterrorFound=false;
        }
        if(empty($primary_id)){
          $emptyPrimaryId[]= $totalRowCsv+2;
          $noterrorFound=false;
        }
        if(empty($crime_category)){
          $emptyCrimeCategory[]= $totalRowCsv+2;
          $noterrorFound=false;
        }

        if(empty($crime_date)){
          $emptyDate[]= $totalRowCsv+2;
          $noterrorFound=false;
        }
        if(empty($address)){
          $emptyAdd[]= $totalRowCsv+2;
          $noterrorFound=false;
        }
        if(empty($type)){
          $emptyType[]= $totalRowCsv+2;
          $noterrorFound=false;
        }
        if(empty($latitude)){
          $emptylat[]= $totalRowCsv+2;
          $noterrorFound=false;
        }
        if($latitude<0 && !empty($latitude)){
          $nagativeLat[]= $totalRowCsv+2;
          $noterrorFound=false;
        }
        if(empty($longitude)){
          $emptylong[]= $totalRowCsv+2;
          $noterrorFound=false;
        }
        if($longitude>0 && !empty($longitude)){
          $possitivelong[]= $totalRowCsv+2;
          $noterrorFound=false;
        }

        if(empty($crime_type)){
          $typeMiss[]= $totalRowCsv+2;
          $noterrorFound=false;
        }
        // Check if variable is empty or not
        if($noterrorFound===true) { 
          // Insert Record
          $wpdb->insert($tablename, array(
            'caseno' =>$caseno,
            'primary_id' =>$primary_id,
            'date' =>$crime_date,
            'crime_time' =>$crime_time,
            'address' =>$address,
            'type' => $crime_type,
            'latLng' =>$latLng,
            'crime_category' =>$crime_category,
            'police_district' => $police_district,
            'psa' =>$psa,
            'created_at' => $current_time,
            'updated_at' => $current_time
          ));
         
          if($wpdb->insert_id > 0){
            $totalInserted++;
          }
        } else {
          echo $caseno."\n";
          $errorCount++;
        }
        $totalRowCsv++;
        // Validation array separated with comma.
        $errorType = array();
        if(!empty($emptyCaseNo)){$errorType[$caseno][0]=1;}
        if(!empty($emptyPrimaryId)){$errorType[$caseno][1]=1;}
        if(!empty($emptyDate)){$errorType[$caseno][2]=1;}
        if(!empty($emptyAdd)){$errorType[$caseno][3]=1;}
        if(!empty($emptyType)){$errorType[$caseno][4]=1;}
        if(!empty($emptylat)){$errorType[$caseno][5]=1;}
        if(!empty($nagativeLat)){$errorType[$caseno][6]=1;}
        if(!empty($emptylong)){$errorType[$caseno][7]=1;}
        if(!empty($possitivelong)){$errorType[$caseno][8]=1;}
        if(!empty($typeMiss)){$errorType[$caseno][9]=1;}
        if(!empty($emptyCrimeCategory)){$errorType[$caseno][10]=1;}
        $errorContainer[] = $errorType;
  }
  fclose($handle);
}

// Display Script End time
$time_end = microtime(true);

//dividing with 60 will give the execution time in minutes, otherwise seconds
$execution_time = ($time_end - $time_start)/60;

//execution time of the script
echo '<b>Total Execution Time:</b> '.$execution_time.' Mins Error Count: '.$errorCount;
//print_r($errorContainer);

?>
