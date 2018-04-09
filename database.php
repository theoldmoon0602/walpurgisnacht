<?php

function connect()
{
	$pdo = new PDO("sqlite:".__DIR__."/database.sqlite");
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

	return $pdo;
}

function init_database($pdo)
{
	$schemas = [
		"create table if not exists timetable (
			id integer primary key,
			classes text,
			day integer
		);",
		"create table if not exists date_timetable (
			id integer primary key,
			classes text,
			date text
		);",
		"create table if not exists events (
			id integer primary key,
			content text,
			date integer
		);",
		"create table if not exists memos (
			id integer primary key,
			content text
		)",
		"create table if not exists serifs (
			id integer primary key,
			content text
		)",
	];

	foreach ($schemas as $schema) {
		$pdo->exec($schema);
	}
}
