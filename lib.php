<?php

require_once 'database.php';

function get_timetable($pdo, $day)
{
	$stmt = $pdo->prepare('select * from timetable where day = :day');
	$stmt->execute([':day' => date('w', $day)]);

	$timetable = [];
	foreach ($stmt->fetchAll() as $row) {
		$timetable = $row;
		$timetable['classes'] = unserialize($row['classes']);
		break;
	}

	return $timetable;
}

function set_timetable($pdo, $day, $classes)
{
	if (count(get_timetable($pdo, $day)) === 0) {
		$stmt = $pdo->prepare('insert into timetable(day, classes) values (:day, :classes)');
		$stmt->execute([':day' => date('w', $day), ':classes' => serialize($classes)]);
	}
	else {
		$stmt = $pdo->prepare('update timetable set classes = :classes where day = :day');
		$stmt->execute([':day' => date('w', $day), ':classes' => serialize($classes)]);
	}
}


function get_timetable_for_date($pdo, $date)
{
	$stmt = $pdo->prepare('select * from date_timetable where date = :date');
	$stmt->execute([':date' => date('Ymd', $date)]);

	$timetable = [];
	foreach ($stmt->fetchAll() as $row) {
		$timetable = $row;
		$timetable['classes'] = unserialize($row['classes']);
		break;
	}

	return $timetable;
}

function set_timetable_for_date($pdo, $date, $classes)
{
	if (count(get_timetable_for_date($pdo, $date)) === 0) {
		$stmt = $pdo->prepare('insert into date_timetable(date, classes) values (:date, :classes)');
		$stmt->execute([':date' => date('Ymd', $date), ':classes' => serialize($classes)]);
	}
	else {
		$stmt = $pdo->prepare('update date_timetable set classes = :classes where date = :date');
		$stmt->execute([':date' => date('Ymd', $date), ':classes' => serialize($classes)]);
	}
}

function get_events_for_date($pdo, $date)
{
	$events = [];
	$stmt = $pdo->prepare('select * from events where date = :date order by date asc');
	$stmt->execute([':date' => date('Ymd', $date)]);
	foreach ($stmt as $row) {
		$events []= $row;
	}

	return $events;
}



function get_events($pdo, $today=null)
{
	if (is_null($today)) {
		$today = date('Ymd', time());
	}

	$events = [];
	$stmt = $pdo->prepare('select * from events where date >= :today order by date asc');
	$stmt->execute([':today' => $today]);
	foreach ($stmt as $row) {
		$events []= $row;
	}

	return $events;
}

function add_event($pdo, $content, $date)
{
	$stmt = $pdo->prepare('insert into events(content, date) values (:content, :date);');
	$stmt->execute([':content' => $content, ':date' => date('Ymd', $date)]);
}

function edit_event($pdo, $id, $content , $date)
{
	$stmt = $pdo->prepare('update events set content = :content, date = :date where id = :id');
	$stmt->execute([':id' => $id, ':content' => $content, ':date' => date('Ymd', $date)]);
}

function delete_event($pdo, $id)
{
	$stmt = $pdo->prepare('delete from events where id = :id');
	$stmt->execute([':id' => $id]);
}

function get_memos($pdo)
{
	$memos = [];
	foreach ($pdo->query('select * from memos order by id asc') as $row) {
		$memos []= $row;
	}

	return $memos;
}

function add_memo($pdo, $content)
{
	$stmt = $pdo->prepare('insert into memos(content) values (:content);');
	$stmt->execute([':content' => $content]);
}

function edit_memo($pdo, $id, $content)
{
	$stmt = $pdo->prepare('update memos set content = :content where id = :id');
	$stmt->execute([':id' => $id, ':content' => $content]);
}

function delete_memo($pdo, $id)
{
	$stmt = $pdo->prepare('delete from memos where id = :id');
	$stmt->execute([':id' => $id]);
}


function get_serifs($pdo)
{
	$serifs = [];
	foreach ($pdo->query('select * from serifs order by id asc') as $row) {
		$serifs []= $row;
	}

	return $serifs;
}

function add_serif($pdo, $content)
{
	$stmt = $pdo->prepare('insert into serifs(content) values (:content);');
	$stmt->execute([':content' => $content]);
}

function edit_serif($pdo, $id, $content)
{
	$stmt = $pdo->prepare('update serifs set content = :content where id = :id');
	$stmt->execute([':id' => $id, ':content' => $content]);
}

function delete_serif($pdo, $id)
{
	$stmt = $pdo->prepare('delete from serifs where id = :id');
	$stmt->execute([':id' => $id]);
}

