<?php

require_once '../define.php';
require_once '../database.php';
require_once '../lib.php';
require_once '../line.php';


function json($data) {
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
}

date_default_timezone_set('Asia/Tokyo');

if (!isset($_GET['query'])) {
  json([]);
}
else {
  $query = $_GET['query'];

  $pdo = connect(); 

  if ($query === 'get_timetable') {
    $date = $_GET['date'];
    $timetable = get_timetable_for_date($pdo, $date);
    if (count($timetable) === 0) {
      json(get_timetable($pdo, (int)$date));
    }
    else {
      json($timetable);
    }
  }
  else if ($query === 'set_timetable') {
    $timetable = [];
    $timetable []= $_GET['1'];
    $timetable []= $_GET['2'];
    $timetable []= $_GET['3'];
    $timetable []= $_GET['4'];
    $timetable []= $_GET['5'];
    $timetable []= $_GET['6'];
    $timetable []= $_GET['7'];
    $timetable []= $_GET['8'];
    $date = $_GET['date'];

    set_timetable($pdo, $date, $timetable);
    json([]);
  }
  else if ($query === 'set_timetable_for_date') {
    $timetable = [];
    $timetable []= $_GET['1'];
    $timetable []= $_GET['2'];
    $timetable []= $_GET['3'];
    $timetable []= $_GET['4'];
    $timetable []= $_GET['5'];
    $timetable []= $_GET['6'];
    $timetable []= $_GET['7'];
    $timetable []= $_GET['8'];
    $date = $_GET['date'];

    set_timetable_for_date($pdo, $date, $timetable);
    json([]);
  }

  else if ($query === 'get_memos') {
    $memos = get_memos($pdo);
    json($memos);
  }
  else if ($query === 'add_memo') {
    $content = $_GET['content'];
    add_memo($pdo, $content);
    json([]);
  }
  else if ($query === 'edit_memo') {
    $id = $_GET['id'];
    $content = $_GET['content'];
    edit_memo($pdo, $id, $content);
    json([]);
  }
  else if ($query === 'delete_memo') {
    $id = $_GET['id'];
    delete_memo($pdo, $id);
    json([]);
  }

  else if ($query === 'get_serifs') {
    $serifs = get_serifs($pdo);
    json($serifs);
  }
  else if ($query === 'add_serif') {
    $content = $_GET['content'];
    add_serif($pdo, $content);
    json([]);
  }
  else if ($query === 'edit_serif') {
    $id = $_GET['id'];
    $content = $_GET['content'];
    edit_serif($pdo, $id, $content);
    json([]);
  }
  else if ($query === 'delete_serif') {
    $id = $_GET['id'];
    delete_serif($pdo, $id);
    json([]);
  }

  else if ($query === 'get_events') {
    $date = $_GET['date'];
    $events = get_events($pdo, date('Ymd', $date));
    json($events);
  }
  else if ($query === 'add_event') {
    $content = $_GET['content'];
    $date = $_GET['date'];
    add_event($pdo, $content, $date);
    json([]);
  }
  else if ($query === 'edit_event') {
    $id = $_GET['id'];
    $content = $_GET['content'];
    $date = $_GET['date'];
    edit_event($pdo, $id, $content, $date);
    json([]);
  }
  else if ($query === 'delete_event') {
    $id = $_GET['id'];
    delete_event($pdo, $id);
    json([]);
  }

  else if ($query === 'upload_image') {
    if (is_uploaded_file($_FILES['file']['tmp_name'])) {
      $finfo = new finfo(FILEINFO_MIME_TYPE);
      $mime_type = $finfo->file($_FILES['file']['tmp_name']);
      $ext = null;

      if ($mime_type === 'image/png') {
        $ext = 'png';
      }
      else if ($mime_type === 'image/jpeg') {
        $ext = 'jpg';
      }

      if ($ext) {
        $images = scandir('images', 1);
        $n = count($images) - 1;

        move_uploaded_file($_FILES['file']['tmp_name'], "images/$n.$ext");
      }
    }

    header('Location: index.php?key=' . CONSTANTS::KEY);
    exit;
  }
  else if ($query === 'get_images') {
    $images = glob('images/*');
    json($images);
  }
  else if ($query === 'ranko_speak') {
    $serif = $_GET['serif'];

    $logger = new PoorLogger("daily.log");
    $request = new Request(CONSTANTS::ACCESS_TOKEN, CONSTANTS::CHANNEL_SECRET, $logger);
    $request->push(CONSTANTS::GROUP_ID, $request->text($serif)); 
  }

  else {
    http_response_code(400);
  }
}



