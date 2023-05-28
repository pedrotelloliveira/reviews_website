<?php
	require 'database_connection.php';

	session_start();

	if (array_key_exists("logout", $_GET)) {

		//unset($_SESSION["id"]);
		session_destroy();
		setcookie("id", "", time() - 3600 );
		$_COOKIE["id"] = "";
		header("Location: index.php");

	} else if (array_key_exists("id", $_SESSION) || array_key_exists("id", $_COOKIE)) {

		header("Location: search_or_add.php");
	}

	$alertInfo = "";

	if (array_key_exists("submit", $_POST)){
		$username = htmlentities($_POST["username"]);
		$userPass = htmlentities($_POST["password"]);

		if(!$username) {
			$alertInfo = "A username is required";
		}

		if(!$userPass) {
			$alertInfo = "A password is required";
		}

		if ($alertInfo != "") {
			$alertInfo = "There are errors in your form: " . $alertInfo ;

		} else {
			if($_POST["signup"]) {
				$sql = "SELECT id
						FROM users 
						WHERE username = ?";
				
				$stmt = $dbh->prepare($sql);
				$stmt->bindParam(1, $username);
				$stmt->execute();
				$results = $stmt->rowCount();

				//checks if there is already a user with the username the new user is trying to resgister with. if there is, it displays a message with that information otherwise it creates a new user in the database
				if ($results > 0 ) {
					$alertInfo = "That username is alredy in use. Please choose another one.";

				} else {
					$sql = "INSERT INTO users (name, username, password, isadmin)
							VALUES (?, ?, ?, 0)";
					
					$newUser = $dbh->prepare($sql);
					$newUser->bindParam(1, $username);
					$newUser->bindParam(2, $username);
					$newUser->bindValue(3, password_hash($userPass, PASSWORD_BCRYPT));
					$status = $newUser->execute();
					$last_id = $dbh->lastInsertId();

					if (!$status) {
						$alertInfo = "Could not sign you up - please try again later.";
					
					} else {
						$_SESSION["id"] = $last_id;

						if ($_POST["stayLoggedIn"] == 1) {
							setcookie("id", $last_id, time() + 60*60*24*365);
						}
						header("Location: search_or_add.php");

					}

				}

			} else {
				$sql = "SELECT * FROM users WHERE username = ? ";

				$existUser = $dbh->prepare($sql);
				$existUser->bindParam(1, $username);
				$existUser->execute();
				$status = $existUser->rowCount();
				$row = $existUser->fetch();

				if ($status) {

					if (password_verify($userPass, $row["password"])) {
						$_SESSION["id"] = $row["ID"];
						
						if ($_POST["stayLoggedIn"] == 1) {
							setcookie("id", $row["ID"], time() + 60*60*24*365);
						}

						header("Location: search_or_add.php");

					} else{
						$alertInfo = "Password is incorrect";
					}

				} else {
					$alertInfo = "That username could not be found";
				}
			}
			
		}

	}

?>

<!DOCTYPE html>

<html>
	<head>
		<title>Sign Up</title>
		<link rel="stylesheet" type="text/css" href="simple.css"/>
	</head>
	<body>
		<h1>Welcome to University Venue Reviews</h1>

		<form method="post">
			<fieldset>
				<legend> Log In </legend>

				<label for="username">Username:</label>
				<input type ="text" name="username"><br/>

				<label for="password">Password </label>
				<input type="password" name="password" > <br/>

				<label for="stayLoggedIn" class="checkbox">
				<input type="checkbox" name="stayLoggedIn" value="1"> Stay Logged In</label>
				
				<input type="hidden" name="signup" value="0">
				
				<input type="submit" name="submit" value="Log In!">
			</fieldset>
		</form>

 
		<form method="post">
			<fieldset>
				<legend> Register </legend>

				<label for="username">Username:</label>
				<input type ="text" name="username"><br/>

				<label for="password">Password </label>
				<input type="password" name="password"> <br/>

				<label for="stayLoggedIn" class="checkbox">
				<input type="checkbox" name="stayLoggedIn" value="1"> Stay Logged In</label>

				<input type="hidden" name="signup" value="1">
				
				<input type="submit" name="submit" value="Sign Up!">
			</fieldset>
		</form>

		<div class="alert">
			<?php echo "<p>" . $alertInfo . "</p>"; ?>
		</div>
	</body>
</html>