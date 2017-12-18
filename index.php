<?php $page_title ="Upcoming Events at Stonegate Pizza and Rum Bar in Tacoma WA"; ?>
<?php $fb_page_id = "252566649502"; ?>


<!DOCTYPE html>
<html lang="en">
<head>
 
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
 
    <title><?php echo $page_title; ?></title>
 
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css?family=Permanent+Marker|Coming+Soon|Righteous|Aclonica|Baloo+Tamma|Ceviche+One|Chewy|Exo:900|Gorditas:700|Kavoon|Limelight|Racing+Sans+One|Shadows+Into+Light|Skranji:700" rel="stylesheet">		
    <link rel="stylesheet" href="css/event-cal.css">
 
</head>
<body>


<div class="page-header">
 
<h1 style="text-align:center;"><?php echo $page_title; ?></h1>

<!--
<div id="demo"></div>

<script>
var txt = "";
txt += "<p>Total width/height: " + screen.width + "*" + screen.height + "</p>";
txt += "<p>Available width/height: " + screen.availWidth + "*" + screen.availHeight + "</p>";
txt += "<p>Color depth: " + screen.colorDepth + "</p>";
txt += "<p>Color resolution: " + screen.pixelDepth + "</p>";

document.getElementById("demo").innerHTML = txt;
</script>

</div>
-->
 
<div class="container">
 
<!-- events will be here -->
<?php
    $year_range = 1;

    // automatically adjust date range
    // human readable years
    //$tomorrow  = mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"));
    $since_date = date('Y-m-d');
    //$until_date = date(DATE_ATOM, mktime(0, 0, 0, date("m")  , date("d")+7, date("Y")));
    $until_date = date('Y-m-d', strtotime('+' . 31 . ' days'));
    //$until_date = date('Y-m-d', strtotime('+' . 1 . ' years'));
    
    //echo "from ";
    //echo $since_date;
    //echo "   to ";
    //echo $until_date;

    // unix timestamp years
    $since_unix_timestamp = strtotime($since_date);
    $until_unix_timestamp = strtotime($until_date);
    //$until_unix_timestamp = $since_unix_timestamp + 604800;  // 7 days of seconds
    //$until_unix_timestamp = strtotime("2018-11-29");    
    
    // or you can set a fix date range:
    // $since_unix_timestamp = strtotime("2012-01-08");
    // $until_unix_timestamp = strtotime("2018-06-28");    

    // Used this to get access token
    // https://graph.facebook.com/oauth/access_token?client_id=1491742964279671&client_secret=108296703bbad539dace7931baa6b9f9&grant_type=client_credentials

    $access_token = "1491742964279671|Wq1I_DvImNsIL4hwSyY4jzjWBeM";

    $fields="id,name,description,place,timezone,start_time,end_time,cover,event_times";
    
    //$json_link = "https://graph.facebook.com/v2.11/{$fb_page_id}/events/?fields={$fields}&access_token={$access_token}&since={$since_unix_timestamp}&until={$until_unix_timestamp}";
    //$json_link = "https://graph.facebook.com/v2.11/{$fb_page_id}/events/?time_filter=upcoming&fields={$fields}&access_token={$access_token}";
    $json_link = "https://graph.facebook.com/v2.11/{$fb_page_id}/events/?time_filter=upcoming&fields={$fields}&access_token={$access_token}";
    
    $json = file_get_contents($json_link);

    $obj = json_decode($json, true, 512, JSON_BIGINT_AS_STRING);
        
    // for those using PHP version older than 5.4, use this instead:
    // $obj = json_decode(preg_replace('/("\w+"):(\d+)/', '\\1:"\\2"', $json), true);

    // count the number of events
    $event_count = count($obj['data']);
    //echo " Found {$event_count} events.";

        // Process recurring events
        for($x=0; $x<$event_count; $x++){
            // Look for recurring event times
            if(isset($obj['data'][$x]['event_times'][0]['start_time']) ? 1 : 0){
                $repeat_count = count($obj['data'][$x]['event_times']);

                // end time is initially the last occurance of the event - next will be earlier
                $obj['data'][$x]['start_time'] = $obj['data'][$x]['end_time'];

                for($y=0; $y<$repeat_count; $y++){
                    // upcoming event?
                    if(strtotime($obj['data'][$x]['event_times'][$y]['start_time']) >= strtotime($since_date)){
                        // and earliest found?
                        if(strtotime($obj['data'][$x]['event_times'][$y]['start_time']) < strtotime($obj['data'][$x]['start_time'])){
                            // show it as next time for this event
                            $obj['data'][$x]['start_time'] = $obj['data'][$x]['event_times'][$y]['start_time'];
                            $obj['data'][$x]['end_time'] = $obj['data'][$x]['event_times'][$y]['end_time'];
                            //$obj['data'][$x]['id'] = $obj['data'][$x]['event_times'][$y]['id'];
                        }
                    }
                }
            }
        }            

        function sortFunction($a,$b){
            if ($a['start_time'] == $b['start_time']) return 0;
            return strtotime($a['start_time']) - strtotime($b['start_time']);
        }
        usort($obj['data'],"sortFunction");        

        echo "<ul class='events'>";
        
        for($x=0; $x<$event_count; $x++){
            // set timezone
            date_default_timezone_set($obj['data'][$x]['timezone']);
            
            $start_date = date( 'l, F d, Y', strtotime($obj['data'][$x]['start_time']));
            $start_time = date('g:i a', strtotime($obj['data'][$x]['start_time']));
            
            $pic_big = isset($obj['data'][$x]['cover']['source']) ? $obj['data'][$x]['cover']['source'] : "https://graph.facebook.com/v2.7/{$fb_page_id}/picture?type=large";
            
            $eid = $obj['data'][$x]['id'];
            $name = $obj['data'][$x]['name'];
            $description = isset($obj['data'][$x]['description']) ? $obj['data'][$x]['description'] : "";
            
            // place
            //$place_name = isset($obj['data'][$x]['place']['name']) ? $obj['data'][$x]['place']['name'] : "";
            //$city = isset($obj['data'][$x]['place']['location']['city']) ? $obj['data'][$x]['place']['location']['city'] : "";
            //$country = isset($obj['data'][$x]['place']['location']['country']) ? $obj['data'][$x]['place']['location']['country'] : "";
            //$zip = isset($obj['data'][$x]['place']['location']['zip']) ? $obj['data'][$x]['place']['location']['zip'] : "";
            
            //$location="";
            
            //if($place_name && $city && $country && $zip){
            //    $location="{$place_name}, {$city}, {$country}, {$zip}";
            //}else{
            //    $location="Location not set or event data is too old.";
            //}

            echo "<li>";

                echo "<div id=cal-day>" . $start_date . "</div>";

                echo "<div class=fanart style='background-image: url(" . $pic_big . ");'></div>";
            
                echo "<div id=cal-title>" .
                    '<table id="tblEvent"><tr><td><span class="event-time">' . $start_time .
                    '</span></td><td class=event-title>' . $name . '</td></tr></table>';
    
                echo '<div id=cal-info><table><tr>';

                    echo '<td class=cal-img><a href="' . $pic_big .
                        '" target="_blank"><img src="' . $pic_big . '"></a></td>';

                    echo '<td class=cal-desc>' . $description .
                        '<br><br><br><div class=event-fblink>' .
                        "<a href='https://www.facebook.com/events/{$eid}/' target='_blank'>View on Facebook</a>";
                    echo '</div></td>';

                 echo '</tr></table></div></div>';

            echo '</li>';
        }
        echo "/ul>";
?>

</div>
 
 
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
 
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
 
</body>
</html>
