<?php
	require 'database_connection.php';

	session_start();

	if (array_key_exists("id", $_COOKIE)) {

		$_SESSION["id"] = $_COOKIE["id"];

	}

	if (array_key_exists("id", $_SESSION)) {
		//information message to tell the user if the venue was sucessfully added or not
		$message = "";
		
		//checks to see if there is an addVenue variable in the the _POST array
		if (array_key_exists("addVenue", $_POST)) {
			//store the variables passed by _POST on the add venue form
			$venueName = htmlentities($_POST["venueName"]);
			$venueType = htmlentities($_POST["venueType"]);
			$description = htmlentities($_POST["description"]);

			if($venueName == "" || $venueType == "" || $description == "") {
				$message = "You need to fill all fields";

			} else {
				//geting the user name to be added to the database with the new venue
				$sql = "SELECT name FROM users WHERE id = ? ";

				$existUser = $dbh->prepare($sql);
				$existUser->bindParam(1, $_SESSION["id"]);
				$existUser->execute();
				$username = $existUser->fetch();
				$status = $existUser->rowCount();

				if (!$status) {

					$message = "There was a problem adding the venue. Please try again later.";

				} else {
					//inserts the new venue into the database
					$sql = "INSERT INTO venues (name, type, description, username)
							VALUES (?, ?, ?, ?)";

					$stmt = $dbh->prepare($sql);
					$stmt->bindParam(1, $venueName);
					$stmt->bindParam(2, $venueType);
					$stmt->bindParam(3, $description);
					$stmt->bindParam(4, $username["name"]);
					$result = $stmt->execute();	
					
					if ($result) {
						$message = "You sucessfully added " . $venueName . " as a new venue";
					}

				}
			}

		}

	} else {
		header("Location: index.php");
	}

?>

<!DOCTYPE html>
<html>
	<head>
		<title>Uni Reviews Site</title>
		<link rel="stylesheet" type="text/css" href="simple.css"/>
	</head>
	<body>
		<a href="index.php?logout=1" class="logout">Logout</a>

		<h2>Search for Venues</h2>
		<div> 
			<form method="get" action="searchresults_venue.php">
				<label for="venueType1">
				<input type="radio" name="venueType" value="restaurant" id="venueType1"> Restaurant</label>

				<label for="venueType2">
				<input type="radio" name="venueType" value="coffeeShop" id="venueType2"> Coffee Shop</label>

				<label for="venueType3">
				<input type="radio" name="venueType" value="bar" id="venueType3"> Bar</label>

				<label for="venueType4">
				<input type="radio" name="venueType" value="all" id="venueType4" checked> All</label>

				<input type="submit" value="Search">
			</form>
		</div>

		<h2>Add new Venue</h2>
		<div>
			<form method="post">
				<label>Venue Name</label>
				<input type="text" name="venueName"><br>

				<label>Type of Venue</label>
				<input type="text" name="venueType"><br>

				<label>Description</label>
				<textarea name="description" rows="4" cols="50" ></textarea><br>

				<input type="submit" name="addVenue" value="Add"><br>
			</form>
		</div>

		<h4>  <?php echo $message; ?> </h4>

	</body>
</html>