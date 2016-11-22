<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'setting.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'CurlClient.php';

use Goutte\Client;
use tools\CurlClient;
use Jitsu\RegexUtil;
use Simplon\Mysql\Mysql;





for (;;){ 

	info('start');
	setClient();
	die;
	$link = connectDb()->fetchRow('SELECT * FROM link WHERE status != :status and type = :type LIMIT 1',[':status' => 'crawled',':type' => 'product']);

	if (empty($link) === false) {
		parsingLink($link);
	}else{
		error('There are not product for crawling');
		die;
	}
	
}

function setClient()
{
	# code...
}

function changeStatusLink($link)
{
	$condr = [
		'id' => $link['id'],		
	];
	$data = [
		'status' => 'crawled',
	];
	updateTable('link', $condr, $data);
	info($link['url'].' is crawled');
}

//database
function connectDb()
{
	require 'config/db.php';
	
	return new Mysql(
	    $config['host'],
	    $config['user'],
	    $config['password'],
	    $config['database']
	);
}

function insertTable($table,$data)
{
	return connectDb()->insertMany($table, $data);	
}
function updateTable($table, $condr, $data)
{
	connectDb()->update($table, $condr, $data);	
}
//database

function error($string)
{
	echo "\033[31m".$string."\033[0m".PHP_EOL;
}

function success($string)
{
	echo "\033[32m".$string."\033[0m".PHP_EOL;
}

function info($string)
{
	echo "\033[33m".$string."\033[0m".PHP_EOL;
}
?>