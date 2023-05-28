<?php
	require 'database_connection.php';

	session_start();

	if (array_key_exists("id", $_COOKIE)) {

		$_SESSION["id"] = $_COOKIE["id"];
	}

	if (array_key_exists("id", $_SESSION)) {
			if(array_key_exists("recommend", $_POST)) {
				//sets establishment to the to the varialbe establishment on the the _POST arrray
				$establishment = htmlentities($_POST["establishment"]);

				$sql = "UPDATE venues 
						SET recommended = recommended + 1
						WHERE ID = ?";

				$stmt = $dbh->prepare($sql);
				$stmt->bindParam(1, $establishment);
				$stmt->execute();        
			}

			
			if (isset($_GET["venueType"])) {
				$venueType = htmlentities($_GET["venueType"]);

				if($venueType != "all"){

					$sql = "SELECT * 
						FROM venues 
						WHERE type = ?
						ORDER BY name";
						
					$stmt = $dbh->prepare($sql);
					$stmt->bindParam(1, $venueType);
					$stmt->execute();

				} else {

					$sql = "SELECT * 
						FROM venues
						ORDER BY name";
						
					$stmt = $dbh->prepare($sql);
					$stmt->execute();

				}
			} else {
				header( "Location: search_or_add.php" ); die;
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
		<title>Venues</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" media="screen" href="simple.css">
	</head>
	<body>
		<a href="index.php?logout=1" class="logout">Logout</a>
		<a href="search_or_add.php" class="backpage">Home</a>
		<h1>Venues</h1>
		<div>
			<table>                        
				<?php if($row = $stmt->fetch()) { ?>
					<tr>
						<th> Name </th>
						<th> Type </th>
						<th> Have been Recomended </th>
						<th> Description </th>
					</tr>
					<?php while($row){?>
						<tr>
							<td> <?php echo $row["name"] ?></td>
							<td> <?php echo $row["type"] ?></td>
							<td> <?php echo $row["recommended"] ?> times</td>
							<td style = "max-width:250px;"> <?php echo $row["description"] ?></td>
							<td>
								<form method="post" id="recommend">
									<input type="hidden" name="establishment" value="<?php echo $row["ID"]?>">
									<input type="submit" name="recommend" value="Recommend">
								</form>
							</td>
							<td><a href='reviews_venue.php<?php echo "?&establishment=" . $row["ID"]?>' ><button>See Reviews</button></a></td>
						</tr>
						<?php $row = $stmt->fetch();}
				} else {
					echo "<p> There was a probem with the search. Please try again later</p>";
					echo $error;
				}
				?>
				
			</table>
		</div>
	</body>
	</html>