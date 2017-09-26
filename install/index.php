<?php
include('config.php');

$db_file = CONFIG_PATH.'/database.php';
$config_file = CONFIG_PATH.'/config.php';
//Sanity Checks
$sanity = TRUE;
$errors = '';
if(!file_exists($db_file) || !file_exists($config_file) || !file_exists($sql_file) && !is_writable($db_file)){
	$sanity = FALSE;
	$errors = '';
	$errors .= ((!file_exists($db_file)) ? 'CI Database file not found at '.$db_file.'.<br />' : ' ' );
	$errors .= ((!file_exists($config_file)) ? 'CI Config file not found at '.$config_file.'.<br />' : ' ' );
	$errors .= ((!file_exists($sql_file)) ? 'Database dump file not found at '.$sql_file.'.<br />' : ' ' );
	$errors .= ((!is_writable($db_file)) ? 'Database config file not writable '.$sql_file.'.<br />' : ' ' );
}
//Do files stuff
$error = FALSE;
if(isset($_POST['database'])){
	$info = TRUE;
	
	$dbname = $_POST['dbname'];
	$dbuser = $_POST['dbuser'];
	$dbpass = $_POST['dbpass'];
	$dbhost = $_POST['dbhost'];
	$dbpref = $_POST['dbprefix'];
	$dbdriver = $_POST['dbdriver'];
	if(isset($dbname) && isset($dbuser) && isset($dbpass) && isset($dbhost) && isset($dbdriver)){
		//Try to connect to DB
		$mysqli = new mysqli($dbhost,$dbuser,$dbpass,'');
		$creds_valid = (($mysqli->connect_error) ? FALSE : TRUE);
		//$mysqli->close();
		if($creds_valid){
			// Create database
			$sql = 'CREATE DATABASE IF NOT EXISTS '.$dbname;
			$dbSuccess = FALSE;
			$dbDumb = FALSE;
			if($mysqli->query($sql)){
				$dbSuccess = TRUE;
			}
			$mysqli->close();
			if($dbSuccess){
				$mysqli = new mysqli($dbhost,$dbuser,$dbpass,$dbname);
				$query = file_get_contents($sql_file);
				if($mysqli->multi_query($query)){
					$dbDumb = TRUE;
				}
				$mysqli->close();
			}
			
			//Save database information
			$template = 'files/database.php';
			$temp = file_get_contents($template);
			$temp = str_replace("%DB_HOSTNAME%", $dbhost, $temp);
			$temp = str_replace("%DB_USERNAME%", $dbuser, $temp);
			$temp = str_replace("%DB_PASSWORD%", $dbpass, $temp);
			$temp = str_replace("%DB_NAME%", $dbname, $temp);
			$temp = str_replace("%DB_DRIVER%", $dbdriver, $temp);
			$temp = str_replace("%DB_PREFIX%", $dbpref, $temp);
			
			@chmod($db_file,0777);
			$handle = fopen($db_file,'w+');
			if(is_writable($db_file)) {

				// Write the file
				if(fwrite($handle,$temp)) {
					$info = TRUE;
				} else {
					$info = FALSE;
					$error = 'Failed to save information';
				}

			} else {
				$info = FALSE;
				$error = "File '$db_file' is not writable. Add write permissions (0777)";
			}
		}else{
			$info = FALSE;
			$error = 'Could not connect to database with the information provided';
		}
		$mysqli->close();
	}else{
		//Error
		$info = FALSE;
		$error = 'Some required fields were not filled';
	}
	unset($_POST);
	if($info && $dbSuccess && $dbDumb){
		header('Location: index.php?page=2');
	}else{
		$error = '';
		if(!$dbSuccess){
			$error .= 'Could not create database. You dont have Database creation priviledges. You have to do it manually.';
		}
		if(!$dbDumb){
			$error .= '<br />Could not execute queries in the file';
		}
	}
}elseif(isset($_POST['config'])){
	//Configuration
	$info = TRUE;
	$baseurl = $_POST['baseurl'];
	$indexpage = $_POST['indexpage'];
	$enckey = $_POST['enckey'];
	if(isset($baseurl) && isset($enckey)){
		@chmod($db_file,0777);
		$handle = fopen($db_file,'w+');
		if(is_writable($db_file)) {
			//Save database information
			$template = 'files/config.php';
			$temp = file_get_contents($template);
			$temp = str_replace("%BASE_URL%", $baseurl, $temp);
			$temp = str_replace("%INDEX_PAGE%", $indexpage, $temp);
			$temp = str_replace("%ENC_KEY%", $enckey, $temp);
			// Write the file
			if(fwrite($handle,$temp)) {
				$info = TRUE;
			} else {
				$info = FALSE;
				$error = 'Failed to save information';
			}

		} else {
			$info = FALSE;
			$error = "File '$db_file' is not writable. Add write permissions (0777)";
		}
	}else{
		$info = FALSE;
		$error = 'Base URL and Encryption Key are required';
	}
	if($info){
		header('Location: index.php?page=3&url='.$baseurl);
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?= 'Install | '.SITE_TITLE; ?></title>
		<style>
			body {
				background-color: #D8D8D8;
				
			}
			.container {
				width: 70%;
				margin: auto;
				background-color:#F9F9F9;
				border: 1px solid #B3B3B3;
				padding:3px;
			}
			.title {
				text-align:center;
			}
			.subtitle {
				text-align:center;
			}
			.content {
				padding-left:3px;
				padding-right:3px;
				text-align:center
			}
			a {
				text-decoration: none;
			}
			.btn {
				background-color: #BDBDBD;
				padding:4px 14px 4px 14px;
				font-size:1.5em;
				border: 1px solid gray;
				text-transform: uppercase;
				color: green;
			}
			.btn:hover{
				background-color:#8B8B8B
			}
			.group {
				margin-bottom:0.5em;
				display: table;
				width:100%;
			}
			form{
				position: relative;
				display: block;
				margin:auto;
			}
			input {
				border: 1px solid #A6FFD4;
				padding:5px;
				width:60%
			}
			select {
				border: 1px solid #A6FFD4;
				padding:5px;
				width:60%;
				background-color: white;
			}
			small {
				color: #FF6069;
			}
			label {
				text-align: left;
				position: relative;
			}
			.error {
				padding: 1em;
				background-color:#FFE8E9;
				color:#FF000E;
				text-align:center;
			}
			.success {
				padding: 1em;
				background-color:#FBFFFC;
				color:#09FF00;
				text-align:center;
			}
		</style>
	</head>
	<body>
		<div class="container">
			<h1 class="title"><?= SITE_TITLE.' Installation' ?></h1>
			<?php
			if($error){
				echo "<div class='error'>".$error."</div>";
			}
			?>
			<?php
			//Pages
			if(isset($_GET['page']) && is_numeric($_GET['page'])){
				if($_GET['page'] == '1'){ ?>
					<h4 class="subtitle">Database Details</h4>
					<div class="content">
						<form action="" method="POST">
							<div>
								<div class="group">
									<label class="label">Database Name</label><br />
									<input type="text" name="dbname" required placeholder="Database Name" class="input">
								</div>
								<div class="group">
									<label class="label">Username</label><br />
									<input type="text" name="dbuser" required placeholder="Username" class="input">
								</div>
								<div class="group">
									<label class="label">Password</label><br />
									<input type="password" name="dbpass" required placeholder="Password" class="input">
								</div>
								<div class="group">
									<label class="label">Database Host</label><br />
									<input type="text" name="dbhost" required placeholder="Database host" class="input" value="localhost">
								</div>
								<div class="group">
									<label class="label">Table Prefix</label><br />
									<input type="text" name="dbprefix" placeholder="Table Prefix" class="input">
								</div>
								<div class="group">
									<label class="label">Database Driver</label><br />
									<label class="label">
										<select required name="dbdriver">
											<option value="mysqli">MySQLi</option>
											<option value="pdo">PDO</option>
											<option value="mysql">MySQL</option>
										</select>
									</label><br />
								</div>
							</div>
							<button class="btn" type="submit" name="database" value="1">Continue</button>
						</form>
					</div>
				<?php }elseif($_GET['page'] == '2'){ ?>
					<h4 class="subtitle">Website Configuration</h4>
					<div class="content">
						<form action="" method="POST">
							<div>
								<div class="group">
									<label class="label">Base URL</label><br />
									<input type="text" name="baseurl" required placeholder="http://example.com/index.php/" class="input"><br /><small>With a trailing slash</small>
								</div>
								<div class="group">
									<label class="label">Index Page</label><br />
									<input type="text" name="indexpage" placeholder="index.php" class="input"><br /><small>Leave blank if using mod_rewrite</small>
								</div>
								<div class="group">
									<label class="label">Encryption Key</label><br />
									<input type="text" name="enckey" required placeholder="Encryption Key" class="input"><br /><small>Random alphanumeric characters</small>
								</div>
							</div>
							<button class="btn" type="submit" name="config" value="1">Continue</button>
						</form>
					</div>
				<?php }elseif($_GET['page'] == '3'){ ?>
					<h4 class="subtitle">Installation Complete</h4>
					<div class="content">
						<p>
							<div class="success">
								Looks like everything was set up correctly.
							</div>
							<div class="error">
								Highly Recommended: Delete the 'install' folder, or move it to a web inaccessible location!
							</div>
							<br />
							<a href="<?php echo $_GET['url']; ?>">Visit Website</a>
							<br />
							<br />
							Hey, don't forget to check out more of my cool PHP scripts at <br /><br />
							<a target="_blank" href="https://github.com/bencodestoo">GitHub</a>
						</p>
					</div>
				<?php }
			}else{ ?>
				<h4 class="subtitle">Welcome to the CodeIgniter 3 Web installer</h4>
				<div class="content">
					<p>Before we get started, you will need to know the following:
						<ol>
							<li>Database Name</li>
							<li>Database Username</li>
							<li>Database Password</li>
							<li>Database Host</li>
							<li>Table Prefix</li>
						</ol>
						We will use this information to create '<strong><i><?= $db_file; ?></i></strong>' database configuration file.
					</p>
					<p>The above information is provided by your Web Host. Contact them if you don't have this information
						<br />
						If all is set...
					</p>
					<p>
						<?php
						if(!$sanity){ ?>
							<div class="error">
								Cannot Continue. Sanity Checks failed!<br />
								<?= $errors; ?>
							</div>
						<?php }else{ ?>
							<a class="btn" href="index.php?page=1">Start</a>
						<?php }
						?>
					</p>
				</div>
			<?php }
			?>
		</div>
	</body>
</html>
