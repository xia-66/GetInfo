<?php

function __($message) {
	$messages = array(
		'User Agent' => 'UA',
		'GeoIP' => 'IP',
		'Time' => 'Time',
		
	);
	if (substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) === 'zh') {
		print isset($messages[$message]) ? $messages[$message] : $message;
	} else {
		print $message;
	}
}

function get_remote_addr()
{
	if (isset($_SERVER["HTTP_X_REAL_IP"])) {
		return $_SERVER["HTTP_X_REAL_IP"];
	} else if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
		return preg_replace('/^.+,\s*/', '', $_SERVER["HTTP_X_FORWARDED_FOR"]);
	} else {
		return $_SERVER["REMOTE_ADDR"];
	}
}

function get_user_agent()
{
   return $_SERVER["HTTP_USER_AGENT"]; 
}

function my_json_encode($a=false)
{
	if (is_null($a))
		return 'null';
	if ($a === false)
		return 'false';
	if ($a === true)
		return 'true';
	if (is_scalar($a)) {
		if (is_float($a)) {
			return floatval(str_replace(',', '.', strval($a)));
		}
		if (is_string($a)) {
			static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
			return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
		}
		else
			return $a;
	}
	$isList = true;
	for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
		if (key($a) !== $i) {
			$isList = false;
			break;
		}
	}
	$result = array();
	if ($isList) {
		foreach ($a as $v)
			$result[] = my_json_encode($v);
		return '[' . join(',', $result) . ']';
	} else {
		foreach ($a as $k => $v)
			$result[] = my_json_encode($k).':'.my_json_encode($v);
		return '{' . join(',', $result) . '}';
	}
}

if (!function_exists('json_encode')) {
	function json_encode($a) {
		return my_json_encode($a);
	}
}

if (isset($_GET['method'])) {
	header("Cache-Control: no-cache, must-revalidate");
	switch ($_GET['method']) {
		case 'raw':
			header('Context-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="index.php"');
			readfile($_SERVER["SCRIPT_FILENAME"]);
			exit;
		case 'sysinfo':
			echo json_encode(array(
				
				'stime' => date('Y年m月d日 H:i:s'),
			));
			exit;
	}
}

$stime = date('Y年m月d日 H:i:s');
$remote_addr = get_remote_addr();
$ua = get_user_agent();

@header("Content-Type: text/html; charset=utf-8");

?><!DOCTYPE html>
<meta charset="utf-8">
<title>请求信息</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<style>
html {
	scroll-behavior: smooth;
}
body {
	margin: 0;
	font-family: "Helvetica Neue", "Luxi Sans", "DejaVu Sans", Tahoma, "Hiragino Sans GB", "Microsoft Yahei", sans-serif;
}
h2 {
	text-align: center;
}
.container {
	padding-right: 25px;
	padding-left: 25px;
	margin-right: auto;
	margin-left: auto;
}
@media(min-width:768px) {
	.container {
		max-width: 750px;
	}
}
@media(min-width:992px) {
	.container {
		max-width: 970px;
	}
}
@media(min-width:1200px) {
	.container {
		max-width: 1170px;
	}
}
</style>

<h2 >请求信息</h2>
<div class="container">

<table>
	<tr>
	<th colspan="4"><?php __('访客信息'); ?></th>
	</tr>
		<tr>
	<td><?php __('Time'); ?></td>
	<td><span id="stime"><?php echo $stime;?></span></td>
	</tr>
	<tr>
	<td><?php __('GeoIP'); ?></td>
	<td colspan="3">
		<span id="remoteip"><?php echo $remote_addr;?></span>
		<span id="iploc"></span>
	</td>
	</tr>
	<tr>
	<td><?php __('User Agent'); ?></td>
	<td colspan="3"><?php echo $ua; ?>
	</td>
	</tr>
	
</table>

</div>

<style>
table {
	width: 100%;
	max-width: 100%;
	border: 1px solid #5cb85c;
	padding: 0;
	border-collapse: collapse;
}
table th {
	font-size: 15px;
}
table tr {
	border: 1px solid #5cb85c;
	padding: 5px;
}
table th {
	background: #5cb85c
}
table th, table td {
	border: 1px solid #5cb85c;
	font-size: 15px;
	line-height: 20px;
	padding: 5px;
	text-align: left;
}
table.table-hover > tbody > tr:hover > td,
table.table-hover > tbody > tr:hover > th {
	background-color: #f5f5f5;
}

}
</style>

<script type="text/javascript">
var dom = {
	element: null,
	get: function (o) {
		function F() { }
		F.prototype = this
		obj = new F()
		obj.element = (typeof o == "object") ? o : document.createElement(o)
		return obj
	},
	width: function (w) {
		if (!this.element)
			return
		this.element.style.width = w
		return this
	},
	html: function (h) {
		if (!this.element)
			return
		this.element.innerHTML = h
		return this
	}
};

$ = function(s) {
	return dom.get(document.getElementById(s.substring(1)))
};

$.getData = function (url, f) {
	var xhr = new XMLHttpRequest();
	xhr.open('GET', url, true)
	xhr.onreadystatechange = function() {
		if (xhr.readyState == 4 && xhr.status == 200) {
			f(xhr.responseText)
		}
	}
	xhr.send()
}

$.getJSON = function (url, f) {
	var xhr = new XMLHttpRequest();
	xhr.open('GET', url, true)
	xhr.onreadystatechange = function() {
		if (xhr.readyState == 4 && xhr.status == 200) {
			if (window.JSON) {
				f(JSON.parse(xhr.responseText))
			} else {
				f((new Function('return ' + xhr.responseText))())
			}
		}
	}
	xhr.send()
}

function getIploc() {
	$.getData('https://myip.ipip.net/', function (data) {
		remoteip = document.getElementById('remoteip').innerText
		ip = data.match(/\d+\.\d+\.\d+\.\d+/)[0]
		iploc = data.substring(ip.length+12)
		if (ip !== remoteip) {
			$("#iploc").html('（检测到分流代理，真实 IP '+ ip + ' | '+ iploc+'）')
		} else {
			$("#iploc").html('| '+iploc)
		}
	})
}

function getSysinfo() {
	$.getJSON('?method=sysinfo', function (data) {
		$('#stime').html(data.stime)
	});
}

window.onload = function() {
	setTimeout(getIploc, 0)
	setInterval(getSysinfo, 1000)
}

</script>
