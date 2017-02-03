<?php

$vPath = './token_store/' ;
$vTarget = 2.74 ;
$vGoogleOutput = "[['Day', 'Al', 'Cranie', 'Doherty', 'Target']," ;
$vGoogleOutput_d = "[['Day', 'Al', 'Cranie', 'Doherty']," ;
$vGoogleOutput_e = "[['Day', 'Al', 'Cranie', 'Doherty']," ;


function fGetAPI($url, $post) {
    $vPostData = http_build_query($post);
    return file_get_contents($url . "?" . $vPostData);
}

function fGetUserAccessToken($vUserName) {
    global $vPath ;
    $vUserDetails = file($vPath . $vUserName, FILE_SKIP_EMPTY_LINES);
    return rtrim($vUserDetails[0]);
}

function fGetUserID($vUserName) {
    global $vPath ;
    $vUserDetails = file($vPath . $vUserName, FILE_SKIP_EMPTY_LINES);
    return rtrim($vUserDetails[1]);
}


// This will loop through all registered people
$files = array_diff(scandir($vPath), array('.', '..')) ;

foreach ($files as $file) {
    $vUserName    = $file ;
    $vAccessToken = fGetUserAccessToken($vUserName) ;
    $vUserID      = fGetUserID($vUserName) ;
    $vPost =  array("after" => 1483228800, "per_page" => 200, "access_token" => $vAccessToken) ;
    $vOutput = json_decode(fGetAPI("https://www.strava.com/api/v3/athlete/activities", $vPost),true) ;
    
    foreach ($vOutput as $vActivity) {
        $vDate      = date( "Y-m-d", strtotime($vActivity['start_date_local'])) ;                  // time string
        $vDate      = date("z", strtotime($vActivity['start_date_local'])) ;     // Day of year of run
        ${$vUserName[$vDate]} = array() ; 
        $vDistance  = $vActivity['distance'] / 1609.34 ;                          // meters converted to miles
        $vDuration  = gmdate("H:i:s",$vActivity['moving_time']) ;      // seconds moving_time or elapsed_time
        $vElevation = $vActivity['total_elevation_gain'] ;              // meters

		if (empty(${$vUserName}[$vDate])) {
			${$vUserName}[$vDate] = array($vDistance, $vDuration, $vElevation);
		} else {
			$vAddedTime = strtotime(${$vUserName}[$vDate][1]) + strtotime($vDuration) ;
			$vAddedTime = date("H:i:s",($vAddedTime));
			
			$vAddedElevation = ${$vUserName}[$vDate][2] + $vElevation ;

			${$vUserName}[$vDate] = array(${$vUserName}[$vDate][0] + $vDistance, $vAddedTime, $vAddedElevation) ;
		}
    }
}

$vAl = 0 ;
$vAl_d = 0 ;
$vAl_e = 0 ;
$vCranie = 0 ;
$vCranie_d = 0 ;
$vCranie_e = 0 ;
$vSte = 0 ;
$vSte_d = 0 ;
$vSte_e = 0 ;
$vTarget = 2.74 ;

function vGetMinutes($str_time) {
    sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
    $time_seconds = (isset($seconds) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes) / 60 / 60;
    return $time_seconds ;
}

// Need to code in foreach ($files as $file) {
// to parse users whoever is added and not a defined list
for ($i=0; $i<date('z') + 1; $i++) {
    if (empty($aarblaster[$i])) {
        $vAl = $vAl ;
        $vAl_d = $vAl_d ;
        $vAl_e = $vAl_e ;
    } else {
        $vAl += $aarblaster[$i][0]  ;
        $vAl_d += vGetMinutes($aarblaster[$i][1])  ;
        $vAl_e += $aarblaster[$i][2]  ;
    }

    if (empty($cranie[$i])) {
        $vCranie = $vCranie ;
        $vCranie_d = $vCranie_d ;
        $vCranie_e = $vCranie_e ;
    } else {
        $vCranie += $cranie[$i][0]  ;
        $vCranie_d += vGetMinutes($cranie[$i][1])   ;
        $vCranie_e += $cranie[$i][2]  ;
    }
    if (empty($ste_doherty[$i])) {
        $vSte = $vSte ;
        $vSte_d = $vSte_d ;
        $vSte_e = $vSte_e ;
    } else {
        $vSte += $ste_doherty[$i][0]  ;
        $vSte_d += vGetMinutes($ste_doherty[$i][1])   ;
        $vSte_e += $ste_doherty[$i][2]   ;
    }
    $vDay = $i + 1 ;
    
    $vDayMonth = date("M-j", strtotime("January 1st +".($vDay)." days") );
    
    $vGoogleOutput .= "['$vDayMonth', $vAl, $vCranie, $vSte, $vTarget ]," ;
    $vGoogleOutput_d .= "['$vDayMonth', $vAl_d, $vCranie_d,$vSte_d] ," ;
    $vGoogleOutput_e .= "['$vDayMonth', $vAl_e, $vCranie_e,$vSte_e] ," ;   
    $vTarget += 2.74 ; 
}


$vGoogleOutput .= "]" ;
$vGoogleOutput_d .= "]" ;
$vGoogleOutput_e .= "]" ;

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


    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart_e);

      function drawChart_e() {
        var data_e = google.visualization.arrayToDataTable(<?php echo $vGoogleOutput_e ;  ?>);

        var options_e = {
          title: 'Elevation in 2017',
          titleFontSize:30,
          vAxis: {title: "Elevation climbed (m)", gridlines: {count: 10}},
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

        var chart_e = new google.visualization.LineChart(document.getElementById('chart_div_e'));

        // Wait for the chart to finish drawing before calling the getImageURI() method.
        google.visualization.events.addListener(chart_e, 'ready', function () {
          chart_div_e.innerHTML = '<img src="' + chart_e.getImageURI() + '">';
          console.log(chart_div_e.innerHTML);
          
        });
        
        
        chart_e.draw(data_e, options_e);
      }
    </script>
    
  </head>
  <body>
    <div id="chart_div" style="width: 900px; height: 500px"></div>
    
    <div id="chart_div_d" style="width: 900px; height: 500px"></div>
    
    <div id="chart_div_e" style="width: 900px; height: 500px"></div>
  </body>
</html>
