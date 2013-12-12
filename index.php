<?php /* index.php ( lilURL implementation ) */

require_once 'includes/conf.php'; // <- site-specific settings
require_once 'includes/lilurl.php'; // <- lilURL class file

$lilurl = new lilURL();
$msg = '';

// if the form has been submitted
if ( isset($_POST['longurl']) )
{
	// escape bad characters from the user's url
	$longurl = trim(mysql_escape_string($_POST['longurl']));

	// set the protocol to not ok by default
	$protocol_ok = false;
	
	// if there's a list of allowed protocols, 
	// check to make sure that the user's url uses one of them
	if ( count($allowed_protocols) )
	{
		foreach ( $allowed_protocols as $ap )
		{
			if ( strtolower(substr($longurl, 0, strlen($ap))) == strtolower($ap) )
			{
				$protocol_ok = true;
				break;
			}
		}
	}
	else // if there's no protocol list, screw all that
	{
		$protocol_ok = true;
	}
		
	// add the url to the database
	if ( $protocol_ok && $lilurl->add_url($longurl) )
	{
		if ( REWRITE ) // mod_rewrite style link
		{
			$url = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).''.$lilurl->get_id($longurl);
		}
		else // regular GET style link
		{
			$url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'].'?id='.$lilurl->get_id($longurl);
		}

		$msg = '<p class="success">Your  URL is: <a href="'.$url.'">'.$url.'</a></p>';
	}
	elseif ( !$protocol_ok )
	{
		$msg = '<p class="error">Invalid protocol!</p>';
	}
	else
	{
		$msg = '<p class="error">Creation of your URL failed for some reason.</p>';
	}
}
else // if the form hasn't been submitted, look for an id to redirect to
{
	if ( isSet($_GET['id']) ) // check GET first
	{
		$id = mysql_escape_string($_GET['id']);
	}
	elseif ( REWRITE ) // check the URI if we're using mod_rewrite
	{
		$explodo = explode('/', $_SERVER['REQUEST_URI']);
		$id = mysql_escape_string($explodo[count($explodo)-1]);
	}
	else // otherwise, just make it empty
	{
		$id = '';
	}
	
	// if the id isn't empty and it's not this file, redirect to it's url
	if ( $id != '' && $id != basename($_SERVER['PHP_SELF']) )
	{
		$location = $lilurl->get_url($id);
		
		if ( $location != -1 )
		{
			header('Location: '.$location);
		}
		else
		{
			$msg = '<p class="error">Sorry, but that URL isn\'t in our database.</p>';
		}
	}
}

// print the form

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html>
	<head>
		<title><?php echo PAGE_TITLE; ?></title>
		<link rel="shortcut icon" href="//i.imgur.com/hPhfmt2.png"/>
        <meta name="viewport" content="width=450">
		<style type="text/css">
		body {
			font: .8em "Trebuchet MS", Verdana, Arial, Sans-Serif;
			text-align: center;
			color: #333;
			background-color: #fff;
			margin: 1em;
			padding-top: 160px;
		}
		
		#container {
			width: 450px;
			margin: 0 auto;
		}
		
		@media all AND (min-width: 930px) {
			#container {
				width: 900px;
			}
		}
		
		h1 {
			float: left;
			background-image: url('//i.imgur.com/LYlI7Bt.png');
			width: 450px;
			height: 530px;
			text-indent: 100%;
			white-space: nowrap;
			overflow: hidden;
			padding: 0;
			margin: -150px 0 1em;
		}

		p {
			width: 450px;
			float: left;
		}
		
		form {
			width: 368px;
			background-color: #eee;
			border: 1px solid #ccc;
			padding: 10px;
			margin-left: 30px;
			float: left;
		}

		fieldset {
			border: 0;
			margin: 0;
			padding: 0;
		}
		
		a {
			color: #09c;
			text-decoration: none;
			font-weight: bold;
		}

		a:visited {
			color: #07a;
		}

		a:hover {
			color: #c30;
		}

		.error, .success {
			font-size: 1.2em;
			font-weight: bold;
		}
		
		.error {
			color: #ff0000;
		}
		
		.success {
			color: #000;
		}
		
		body > p {
			display: none;
		}
		
		</style>
	</head>
	<body onload="document.getElementById('longurl').focus()">
		<?php echo $msg; ?>

		<div id="container">
			<h1><?php echo PAGE_TITLE; ?></h1>
			<form action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
				<fieldset>
					<label for="longurl">Enter a long URL:</label>
					<input type="text" name="longurl" id="longurl" />
					<input type="submit" name="submit" id="submit" value="Make it short!" />
				</fieldset>
			</form>
			<?php echo $msg; ?>

			<p>The official URL shortener of <a href="http://rainbowdash.net">Rainbow Dash Network</a></p>
			<p>Powered by <a href="http://lilurl.sourceforge.net">lilURL</a></p>
		</div>
	</body>
</html>
		
