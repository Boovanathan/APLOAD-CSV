<!DOCTYPE html>
<html lang="en">
<head>
  <title>PHP MySQLi Example for Dynamically Populate Table from CSV File</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
<br>
<?php
$conn = mysqli_connect("localhost", "root", "", "test");
if(mysqli_connect_error())
{
  echo "<p>There is something wrong happend when connecting to the database.</p>";
  exit();
}
if (isset($_POST["submit"])) {
    $response = "";
    // Get file extension
	$file_extension = pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION);
	$file_name = pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_FILENAME);
    // Validate file input to check if is not empty
$table_name = $file_name;

// get structure from csv and insert db
ini_set('auto_detect_line_endings',TRUE);
$handle = fopen($_FILES["fileToUpload"]["tmp_name"],'r');
// first row, structure
if ( ($data = fgetcsv($handle) ) === FALSE ) {
    echo "Cannot read from csv file";die();
}
$fields = array();
$field_count = 0;
$password_field = -1;
for($i=0;$i<count($data); $i++) {
    $f = strtolower(trim($data[$i]));
    if ($f) {
        // normalize the field name, strip to 20 chars if too long
        $f = substr(preg_replace ('/[^0-9a-z]/', '_', $f), 0, 20);
        $field_count++;
        $fields[] = $f.' VARCHAR(50)';
		if($f=="password"){
		$password_field=$i;
		echo "Column $password_field contain field name password";
		}
    }
}

$sqlQuery = "CREATE OR REPLACE TABLE $table_name (" . implode(', ', $fields) . ')';
//echo $sqlQuery . "<br />";
$returnResult = $conn->query($sqlQuery);
if($returnResult)
{
	echo "<div class='alert alert-success alert-dismissible'><a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>Table $table_name created succesfully :)</div>";
}
else 
{
	echo "<div class='alert alert-danger alert-dismissible'><a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>Error while creating table :(</div>";
  exit();
}
$count=0;
while ( ($data = fgetcsv($handle) ) !== FALSE ) {
    $fields = array();
    for($i=0;$i<$field_count; $i++) {
		$fields[$i] = '\''.addslashes($data[$i]).'\'';
		
		if($password_field == $i)
		{
			echo "md5 encryption processing";
			$fields[$i] = '\''.addslashes(md5($data[$i])).'\'';
		}
	}
    $sqlQuery = "Insert into $table_name values(" . implode(', ', $fields) . ')';
    //echo $sqlQuery;
	$returnResult = $conn->query($sqlQuery);
	if($returnResult)
	{
		echo "<div class='alert alert-success alert-dismissible'><a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>1 row inserted succesfully :)</div>";
	}
	else 
	{
		echo "<div class='alert alert-success alert-dismissible'><a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>Error while inserting into the table :(</div>";
	  exit();
	}
}
fclose($handle);
ini_set('auto_detect_line_endings',FALSE);
print_r($response);
}
?>
  <h2>PHP MySQLi Example for Dynamically Populate Table from CSV File</h2>
  <form action="#" method="POST" enctype="multipart/form-data">
    <div class="form-group">
      <label for="email">Select File:</label>
      <input type="file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" class="form-control" id="fileToUpload" name="fileToUpload">
    </div>
    <input type="submit" name="submit" class="btn btn-primary btn-block"/>
  </form>
</div>

</body>
</html>
