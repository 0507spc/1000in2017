<?php

//http://en-gb.smashrun.com/alexarblaster/overview/2017
//http://en-gb.smashrun.com/alexarblaster/overview/2017/1
//reportConfig.goals
// 1.6 for miles to km


// array of users.
// process and get 3 arrays for the lines
// plot current progress based on distance and days

$vPeopleArray = array('alexarblaster','cranie','ste.doherty') ;
//$vPeopleArray = array('cranie') ;

for ($i=0; $i<count($vPeopleArray); $i++) {
// this will get a list of the runs and distance:
// http://en-gb.smashrun.com/cranie/list/2017
// runs.report.showRun  get these lines then join pairs this will give date and a value km run

$fp = fopen("http://en-gb.smashrun.com/" . $vPeopleArray[$i] . "/list/2017", "rb");
if (FALSE === $fp) {
    exit("Failed to open stream to URL");
}

$vResultList = '';

while (!feof($fp)) {
    $vResultList .= fread($fp, 8192);
}
fclose($fp);

preg_match_all('/runs.report.showRun.*/',$vResultList, $vMatchList) ;

//print_r($vMatchList) ;
${$vPeopleArray[$i]} = array() ;

for ($x=0; $x<count($vMatchList[0]); $x+=7) {
    $vDate = preg_replace('/.*date\"./','',$vMatchList[0][$x]) ;
    $vDate = preg_replace('/2017.*/','2017',$vDate) ;
    $vDate = date('z', strtotime($vDate)) ;
    
    $vDistance = preg_replace('/.*distance\"./','',$vMatchList[0][$x + 2]) ;
    $vDistance = preg_replace('/.mile.*/','',$vDistance) ;
    
    $vDuration = preg_replace('/.*duration\"../','',$vMatchList[0][$x + 5]) ;
    $vDuration = preg_replace('/.*div.*value\">/','',$vDuration) ;
    $vDuration = preg_replace('/<.* .*/','',$vDuration) ;

    if (strlen($vDuration) == 5 ) {
        $vDuration = "00:" . $vDuration ;
    } 
    if (strlen($vDuration) == 7 ) {
        $vDuration = "0" . $vDuration ;
    } 

    if (empty(${$vPeopleArray[$i]}[$vDate])) {
        //${$vPeopleArray[$i]}[$vDate] = $vDistance;
        ${$vPeopleArray[$i]}[$vDate] = array($vDistance, $vDuration);
    } else {
        //${$vPeopleArray[$i]}[$vDate] = ${$vPeopleArray[$i]}[$vDate] + $vDistance ;
        $vAddedTime = strtotime(${$vPeopleArray[$i]}[$vDate][1]) + strtotime($vDuration) ;
        $vAddedTime = date("H:i:s",($vAddedTime));

        ${$vPeopleArray[$i]}[$vDate] = array(${$vPeopleArray[$i]}[$vDate][0] + $vDistance, $vAddedTime) ;
    }

  
}

//print_r(${$vPeopleArray[$i]});

//exit() ;

/// -------------------------

$fp = fopen("http://en-gb.smashrun.com/" . $vPeopleArray[$i] . "/overview/2017", "rb");
if (FALSE === $fp) {
    exit("Failed to open stream to URL");
}

$result = '';

while (!feof($fp)) {
    $result .= fread($fp, 8192);
}
fclose($fp);

preg_match('/reportConfig.goals.*/', $result, $matches, PREG_OFFSET_CAPTURE);

//reportConfig.goals = [{"userId":0,"distance":17,"title":"Goal for 2017","daysInPeriod":365,"id":0,"year":2017,"month":null,"goalText":"","goalKilometers":1609.344,"dateUpdatedUTC":new Date(-62135578800000),"isDeleted":false}];

$vOutput = preg_replace('/.*\[/', '', $matches[0][0]);
$vOutput = preg_replace('/\].*/', '', $vOutput);
$vOutput = preg_replace('/,"dateUpdatedUTC.*/', '}', $vOutput);


//{"userId":0,"distance":17,"title":"Goal for 2017","daysInPeriod":365,"id":0,"year":2017,"month":null,"goalText":"","goalKilometers":1609.344,"dateUpdatedUTC":new Date(-62135578800000),"isDeleted":false}

$vOutputArray = (json_decode($vOutput, true));
/*
echo "Processing for: " . $vPeopleArray[$i]  ;
echo "<p>" ;
echo $vOutputArray['distance']  . "miles";
echo "<p>" ;
//echo $vOutputArray['goalKilometers'] . "km" ;
echo "1000 miles" ;
echo "<p>" ;
*/

}

// 2.74 miles per day

// loop through 0 - today
// get all values for people into format:
// day, name, name, name, target
// use: to fill in blanks

/*
if (empty(${$vPeopleArray[$i]}[$vDate])) {
        ${$vPeopleArray[$i]}[$vDate] = $vDistance;
    } else {
        ${$vPeopleArray[$i]}[$vDate] = ${$vPeopleArray[$i]}[$vDate] + $vDistance ;
    }
*/
//'alexarblaster','cranie','ste.doherty'

$vGoogleOutput = "[['Day', 'Al', 'Cranie', 'Doherty', 'Target']," ;
$vGoogleOutput_d = "[['Day', 'Al', 'Cranie', 'Doherty']," ;

$vAl = 0 ;
$vAl_d = 0 ;
$vCranie = 0 ;
$vCranie_d = 0 ;
$vSte = 0 ;
$vSte_d = 0 ;
$vTarget = 2.74 ;


// need to proper add time here!!!!
//$vAddedTime = strtotime(${$vPeopleArray[$i]}[$vDate][1]) + strtotime($vDuration) ;
//$vAddedTime = date("H:i:s",($vAddedTime));
function vGetMinutes($str_time) {
    sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
    $time_seconds = (isset($seconds) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes) / 60 / 60;
    return $time_seconds ;
}

for ($i=0; $i<date('z') + 1; $i++) {
    if (empty($alexarblaster[$i])) {
        $vAl = $vAl ;
        $vAl_d = $vAl_d ;
    } else {
        $vAl += $alexarblaster[$i][0]  ;
        $vAl_d += vGetMinutes($alexarblaster[$i][1])  ;
    }
    if (empty($cranie[$i])) {
        $vCranie = $vCranie ;
        $vCranie_d = $vCranie_d ;
    } else {
        $vCranie += $cranie[$i][0]  ;
        $vCranie_d += vGetMinutes($cranie[$i][1])   ;
    }
    $vAwkward = 'ste.doherty' ;
    if (empty(${$vAwkward}[$i])) {
        $vSte = $vSte ;
        $vSte_d = $vSte_d ;
    } else {
        $vSte += ${$vAwkward}[$i][0]  ;
        $vSte_d += vGetMinutes(${$vAwkward}[$i][1])   ;
    }
    $vDay = $i + 1 ;
    $vGoogleOutput .= "['$vDay', $vAl, $vCranie, $vSte, $vTarget ]," ;
// duration output



    $vGoogleOutput_d .= "['$vDay', $vAl_d, $vCranie_d,$vSte_d] ," ;

    $vTarget += 2.74 ; 
}

$vGoogleOutput .= "]" ;
$vGoogleOutput_d .= "]" ;

//echo $vGoogleOutput_d ; 
//print_r(array_keys(get_defined_vars()));

?>

  <html>
  <head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var data = google.visualization.arrayToDataTable(<?php echo $vGoogleOutput ;  ?>);

        var options = {
          title: '1000M in 2017',
          titleFontSize:30,
          vAxis: {title: "Miles run", gridlines: {count: 10}},
          backgroundColor: '#E4E4E4',
          series: {3: { lineDashStyle: [4, 1] }},
          lineWidth: 4,
          hAxis: {
              title: "Day",
              gridlines: {
                 color: "#CCCCCC"
              },
              baselineColor:  "#CCCCCC"
          },
          legend: { position: 'bottom' }
        };

        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));

        // Wait for the chart to finish drawing before calling the getImageURI() method.
        google.visualization.events.addListener(chart, 'ready', function () {
          chart_div.innerHTML = '<img src="' + chart.getImageURI() + '">';
          console.log(chart_div.innerHTML);
          
        });
        
        
        chart.draw(data, options);
      }
    </script>

    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart_d);

      function drawChart_d() {
        var data_d = google.visualization.arrayToDataTable(<?php echo $vGoogleOutput_d ;  ?>);

        var options_d = {
          title: 'Hours run in 2017',
          titleFontSize:30,
          vAxis: {title: "Duration run (H)", gridlines: {count: 10}},
          backgroundColor: '#E4E4E4',
          series: {3: { lineDashStyle: [4, 1] }},
          lineWidth: 4,
          hAxis: {
              title: "Day",
              gridlines: {
                 color: "#CCCCCC"
              },
              baselineColor:  "#CCCCCC"
          },
          legend: { position: 'bottom' }
        };

        var chart_d = new google.visualization.LineChart(document.getElementById('chart_div_d'));

        // Wait for the chart to finish drawing before calling the getImageURI() method.
        google.visualization.events.addListener(chart_d, 'ready', function () {
          chart_div_d.innerHTML = '<img src="' + chart_d.getImageURI() + '">';
          console.log(chart_div_d.innerHTML);
          
        });
        
        
        chart_d.draw(data_d, options_d);
      }
    </script>
    
  </head>
  <body>
    <div id="chart_div" style="width: 900px; height: 500px"></div>
    
    <div id="chart_div_d" style="width: 900px; height: 500px"></div>
  </body>
</html>
