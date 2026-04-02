<?php

$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$age = $_POST['age'];
$dob = $_POST['dob'];
$gender = $_POST['gender'] ?? "";
$course = $_POST['course'];
$marks = $_POST['marks'];
$city = $_POST['city'];
$state = $_POST['state'];
$address = $_POST['address'];

$errors = [];

// Validation
if(empty($name)) $errors[] = "Name required";
if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email required";
if(!preg_match("/^[0-9]{10}$/", $phone)) $errors[] = "Valid phone required";
if(empty($age)) $errors[] = "Age required";
if(empty($dob)) $errors[] = "DOB required";
if(empty($gender)) $errors[] = "Gender required";
if(empty($course)) $errors[] = "Course required";
if(empty($marks)) $errors[] = "Marks required";
if(empty($city)) $errors[] = "City required";
if(empty($state)) $errors[] = "State required";
if(empty($address)) $errors[] = "Address required";

?>

<!DOCTYPE html>
<html>
<head>
<title>Form Result</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

<?php

if(!empty($errors)){
    echo "<h3 class='error'>Errors:</h3>";
    foreach($errors as $e){
        echo "<p class='error'>$e</p>";
    }
    echo "<a href='form.php'>Go Back</a>";
}
else{
    echo "<h2 class='success'>Form Submitted Successfully!</h2>";

    echo "<p><b>Name:</b> $name</p>";
    echo "<p><b>Email:</b> $email</p>";
    echo "<p><b>Phone:</b> $phone</p>";
    echo "<p><b>Age:</b> $age</p>";
    echo "<p><b>DOB:</b> $dob</p>";
    echo "<p><b>Gender:</b> $gender</p>";
    echo "<p><b>Course:</b> $course</p>";
    echo "<p><b>Marks:</b> $marks%</p>";
    echo "<p><b>City:</b> $city</p>";
    echo "<p><b>State:</b> $state</p>";
    echo "<p><b>Address:</b> $address</p>";
}

?>

</div>

</body>
</html>