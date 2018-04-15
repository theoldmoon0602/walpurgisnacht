<?php

require_once 'database.php';
require_once 'define.php';
require_once 'line.php';
require_once 'lib.php';

date_default_timezone_set('Asia/Tokyo');
function select_serif($pdo)
{
  $serifs = get_serifs($pdo);
  $r = mt_rand(0, count($serifs)-1);

  return $serifs[$r]['content'];
}

function construct_timetable($pdo)
{
  $date = time();
  $timetable = get_timetable_for_date($pdo, $date);
  if (count($timetable) === 0) {
    $timetable = get_timetable($pdo, $date);
  }
  $timetable = $timetable['classes']; 

  $str = '';
  $pre = null;
  for ($i = 0; $i < count($timetable); $i++) {
    if (is_null($pre)) {
      $pre = [
        't' => [(string)($i+1)],
        'c' => $timetable[$i],
      ];
    }
    else if ($pre['c'] == $timetable[$i]) {
      $pre['t'][] = (string)($i+1);
    }
    else {
      $str = $str . implode(',', $pre['t']) . ': ' . $pre['c'] . "\n";
      $pre = [
        't' => [(string)($i+1)],
        'c' => $timetable[$i],
      ];
    }
  }
  if (! is_null($pre)) {
    $str = $str . implode(',', $pre['t']) . ': ' . $pre['c'] . "\n";
  }

  $events = get_events_for_date($pdo, $date);
  foreach ($events as $event) {
    $str = $str . $event['content'] . "\n";
  }
  return $str;
}

function process_serif($request, $serif, $timetable)
{
  chdir(__DIR__.'/public');

  preg_match_all('/__\d+__/', $serif, $matches);
  $serif = preg_replace('/__\d+__/', '', $serif);
  $serif = str_replace("__TIMETABLE__", $timetable, $serif);

  $img_files = [];
  foreach ($matches[0] as $img_number) {
    $img_number = str_replace("__", "", $img_number);
    $img_file = glob("images/$img_number*")[0];
    $img_files []= substr($img_file, 7);
  }

  $request->push(CONSTANTS::GROUP_ID, $request->text($serif)); 

  foreach ($img_files as $img) {
    $url = "https://theoldmoon0602.tk/kanzakiranko/img.php?file=$img";
    $thumb = $url."&thumb";
    $request->push(CONSTANTS::GROUP_ID, $request->image($url, $thumb));
  }
}



$logger = new PoorLogger("daily.log");
$request = new Request(CONSTANTS::ACCESS_TOKEN, CONSTANTS::CHANNEL_SECRET, $logger);

$pdo = connect();
$timetable = construct_timetable($pdo);
$serif = select_serif($pdo);
process_serif($request, $serif, $timetable);
