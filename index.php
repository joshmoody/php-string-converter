<?php
ini_set('display_errors', false);
function generate_guid()
{
	
	// The field names refer to RFC 4122 section 4.1.2

	return sprintf('%04x%04x-%04x-%03x4-%04x-%04x%04x%04x',
		mt_rand(0, 65535), mt_rand(0, 65535), // 32 bits for "time_low"
		mt_rand(0, 65535), // 16 bits for "time_mid"
		mt_rand(0, 4095),  // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
		bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
			// 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
			// (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
			// 8 bits for "clk_seq_low"
		mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535) // 48 bits for "node"
	);
}

if (!function_exists('mysql_escape_string'))
{
	function mysql_escape_string($unescaped_string = null)
	{
		/**
		 * https://www.php.net/manual/en/function.mysql-real-escape-string.php
		 * mysql_real_escape_string() calls MySQL's library function
		 * mysql_real_escape_string, which prepends backslashes to the following characters:
		 * \x00, \n, \r, \, ', " and \x1a.
		 */
		$search = [
			'\x00', '\n', '\r', '\\', "'", '"', '\xla'
		];
		$replacements = [];
		foreach($search as $s){
			$replacements[] = '\\' . $s;
		}

		return str_replace($search, $replacements, $unescaped_string);
	}
}

$operations['urlencode'] = 'urlencode()';
$operations['urldecode'] = 'urldecode()';
$operations['guid'] = 'Simulate a GUID';
$operations['md5'] = 'md5()';
$operations['sha1'] = 'sha1()';
$operations['unique_id'] = 'uniqid()';
$operations['sha1_unique_id'] = 'sha1 unique id';
$operations['base64_encode'] = 'base64_encode()';
$operations['base64_decode'] = 'base64_decode()';
$operations['parse_str'] = 'Parse a query string into an array';
$operations['htmlentities'] = 'htmlentities';
$operations['html_entity_decode'] = 'html_entity_decode';
$operations['javascript_write'] = 'javascript: document.write()';
$operations['json_decode'] = 'json decode';
$operations['preg_quote'] = 'Prepare string to be used in regular expression';
$operations['strtotime'] = 'Convert human readable date/time string to a unix time stamp';
$operations['timestamp_to_date'] = "Convert a unix timestamp to a formatted date";
$operations['datestamp'] = "Generate a Date/Time Stamp";
$operations['unserialize'] = "unserialize()";

if (array_key_exists('operation', $_POST)){

    try{
        switch($_POST['operation']){
            case 'urlencode':
                $result = urlencode($_POST['string']);
                break;
            case 'urldecode':
                $result = urldecode($_POST['string']);
                break;
            case 'guid':
                $result = generate_guid();
                break;
            case 'md5':
                $result = md5($_POST['string']);
                break;
            case 'sha1':
                $result = sha1($_POST['string']);
                break;
            case 'unique_id';
                $result = $_POST['string'] ? uniqid($_POST['string']) : uniqid ();
                break;
            case 'sha1_unique_id':
                $result = sha1(uniqid($_POST['string'], TRUE));
                break;
            case 'base64_encode':
                $result = base64_encode($_POST['string']);
                break;
            case 'base64_decode':
                $result = base64_decode($_POST['string']);
                break;
            case 'parse_str':
                parse_str($_POST['string'], $result);
                $result = print_r($result, TRUE);
                break;
            case 'htmlentities':
                $result = htmlentities(htmlentities($_POST['string'], ENT_QUOTES, 'ISO-8859-1'), ENT_QUOTES, 'ISO-8859-1');
                break;
            case 'html_entity_decode':
                $result = html_entity_decode($_POST['string']);
                break;
            case 'javascript_write':
                $result = 'document.write("' . mysql_escape_string(str_replace("\r\n", "", utf8_encode($_POST['string']))) . '");';
                break;
            case 'json_decode':
                $result = print_r(json_decode($_POST['string'], TRUE), TRUE);
                break;
            case 'preg_quote':
                $result = preg_quote($_POST['string'], '/');
                break;
            case 'strtotime':
                $result = strtotime($_POST['string']);
                break;
            case 'timestamp_to_date':
                $result = date('Y-m-d H:i:s', $_POST['string']);
                break;
            case 'datestamp':
                $result = date('YmdHis') . str_pad(rand(1,999), 3, '0', STR_PAD_LEFT);
                break;
            case 'unserialize':
                $result = print_r(unserialize($_POST['string']), TRUE);
                break;
        }
	} catch (\Throwable $e) {
        $result = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>String Converter</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
	<link href="//netdna.bootstrapcdn.com/bootswatch/latest/cosmo/bootstrap.min.css" rel="stylesheet" type="text/css">

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/twitter-bootstrap/latest/js/bootstrap.min.js"></script>

	<style>
	/*
	 * Update our padding and margin.
	 */
	body, #top-nav{
	        padding: 0;
	        margin: 0;
	    }

	div#outer-container{
		margin: 0 -10px 0 -10px;
		padding: 10px 20px 60px 20px;
	}

	/* Desktop */
	@media (min-width: 768px) {
		
		div#outer-container{
			margin: 0;
			padding: 10px 20px 100px 10px;
		
		}	
	}
	
	textarea{
		width: 100%;
	}
	</style>
</head>

<body>
<div class="container">
	<h1>String Converter</h1>
	
	<form method="POST">
	
	<label for="operation">Operation</label>
	<select id="operation" name="operation" class="input-xxlarge">
		<?php foreach ($operations as $k=>$v):?>
			<option value="<?php echo $k;?>" <?php echo array_key_exists('operation', $_POST) && $_POST['operation'] == $k ? 'selected="selected"' : ''?>><?php echo $v;?></option>
		<?php endforeach; ?>
	</select>
	
	<div class="row-fluid">
			<div class="span6">
				<label for="string">Original String</label>
				<textarea rows="10" cols="80" id="string" name="string"><?php echo array_key_exists('string', $_POST) ? $_POST['string'] : '';?></textarea>
			</div>
			
			<div class="span6">
				<label for="result">Result</label>
				<textarea rows="10" cols="80" id="result" name="result"><?php echo isset($result) ? $result : '';?></textarea>
			</div>
	
			<input type="submit">
	</div>
	</form>
	
	<?php if (isset($result)):?>
		<p>Result Length: <?php echo strlen($result);?></p>
	<?php endif;?>
	
</div>
</body>
</html>
