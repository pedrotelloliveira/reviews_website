<?php
	require 'database_connection.php';

	session_start();

	if (array_key_exists("id", $_COOKIE)) {

		$_SESSION["id"] = $_COOKIE["id"];

	}

	if (array_key_exists("id", $_SESSION)) {

		$message = "";

		if (array_key_exists("establishment", $_GET)){
			$venueID = htmlentities ($_GET["establishment"]);

			if (isset($_POST["submit"])) {

				$sql = "UPDATE reviews 
						SET approved = 1
						WHERE ID = ?";

				foreach($_POST["submit"] as $checkedBox) {
					$updateApproved = $dbh->prepare($sql);
					$updateApproved->bindParam(1, $checkedBox);
					$updateApproved->execute();
				}

				if (!$updateApproved) {
					$message = $dbh->errorInfo();
				}

			}

			//used to get the name of the user and if it is an admin or not
			$sql = "SELECT * FROM users WHERE id = ? ";
				$existUser = $dbh->prepare($sql);
				$existUser->bindParam(1, $_SESSION["id"]);
				$existUser->execute();
				$user = $existUser->fetch();

			//used to get the name of the venue the user chose to see and also to insert that information in case the user decided to add a new review
			$sql = "SELECT * FROM venues WHERE ID = ?";
				$stmt = $dbh->prepare($sql);
				$stmt->bindParam(1, $venueID);
				$stmt->execute();
				$venueName = $stmt->fetch();
			
			//if there is a description variable on the _GET array, sets it to $description and adds the new review to the databse
			if(array_key_exists("description", $_POST)){
				$description = htmlentities ($_POST["description"]);
					$approved = 0;

				if(!empty($description)) {
					if ($user["isadmin"]){
						$approved = 1;
					}
					
					$sql = "INSERT INTO reviews (venueID, username, review, approved)
							VALUES (?, ?, ?, $approved)"; 
					$stmt = $dbh->prepare($sql);
					$stmt->bindParam(1, $venueName["ID"]);
					$stmt->bindParam(2, $user["name"]);
					$stmt->bindParam(3, $description);
					$stmt->execute();

					if (!$user["isadmin"]){
						$message = "Review pending aproval.";
					} else{
						$message = "Review added successfully";
					}
				
				} else{
					$message = "You can not submit an empty review!";
				}

			}

			//selects the user name and the reviews for a specific venue(used to display this information back to the user)
			$sql = "SELECT reviews.*, venues.name
					FROM reviews
					INNER JOIN venues ON reviews.venueID = venues.ID
					WHERE venueID = ?";

			$stmt = $dbh->prepare($sql);
			$stmt->bindParam(1, $venueID);
			$stmt->execute();
			$reviewsResult = $stmt->fetchAll();
		} else{
			header("Location: search_or_add.php");
		}

	} else {
		header("Location: index.php");
	}


?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Page Title</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" media="screen" href="simple.css">
</head>
<body>
	<a href="index.php?logout=1" class="logout">Logout</a>
	<a href="search_or_add.php" class="backpage">Home</a>

	<!-- if there are any reviews for the venues chosen by the user it will display them otherwise it will prompt the user to add a new one -->
	<?php if ($reviewsResult) { ?>

		<h1>Reviews for<?php echo " " . $venueName["name"] ?>:</h1>
		<div>
			<table>                        
				<tr>
					<th> User </th>
					<th> Review </th>
				</tr>

				<?php foreach($reviewsResult as $id){
					
					if ($id["approved"]) {?>
					<tr>
						<td> <?php echo $id["username"] ?></td>
						<td> <?php echo $id["review"] ?></td>
					</tr><?php }        
				} ?>
			</table>
		</div>

		<?php if ($reviewsResult && $user["isadmin"]) { 
			if (in_array(0, array_column($reviewsResult, 'approved'))) { ?>
			
			<h3>Needs approval:</h3>
			<div>
				<form method="post" id="reviewsForm">
					<table>                        
						<tr>
							<th> User </th>
							<th> Review </th>
						</tr>

						<?php foreach($reviewsResult as $id){
						  if (!$id["approved"]) {?>
							<tr>
								<td> <?php echo $id["username"] ?></td>
								<td> <?php echo $id["review"] ?></td>
								<td id="reviewsCheckBox">
									<input type="checkbox" name="submit[]" value="<?php echo $id["ID"]; ?>">
								</td>
							</tr><?php }
						} ?>
					</table>
					<div id="reviewsSubmit"><input type="submit" value="Save Changes"><div>
				</form>
			</div>

		<?php }}
	} else {
	  echo "<h1>There are no reviews for " . $venueName["name"] . "</h1><p>Be the first to do it.";
	} ?>

	<form method="post">
		<label>New Review</label>
		<textarea name="description" rows="4" cols="50" ></textarea><br>
		<input type="hidden" name="establishment" value="<?php echo $venueName["ID"]?>">
		<input type="submit" name="submitReview" value="Submit Review" /><br>
	</form>
	<h4>  <?php echo $message; ?> </h4>
</body>
</html>