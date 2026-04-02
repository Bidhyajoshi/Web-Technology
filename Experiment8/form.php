<!DOCTYPE html>
<html>
<head>
<title>College Admission Form</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
<h2>🎓 College Admission Form</h2>

<form method="POST" action="process.php">

<div class="form-group">
<label>Name</label>
<input type="text" name="name">
</div>

<div class="form-group">
<label>Email</label>
<input type="text" name="email">
</div>

<div class="form-group">
<label>Phone</label>
<input type="tel" name="phone">
</div>

<div class="form-group">
<label>Age</label>
<input type="number" name="age">
</div>

<div class="form-group">
<label>Date of Birth</label>
<input type="date" name="dob">
</div>

<div class="form-group">
<label>Gender</label>
<div class="radio-group">
<label><input type="radio" name="gender" value="Male"> Male</label>
<label><input type="radio" name="gender" value="Female"> Female</label>
</div>
</div>

<div class="form-group">
<label>Course</label>
<select name="course">
<option value="">Select</option>
<option>BCA</option>
<option>BSc</option>
<option>BTech</option>
</select>
</div>

<div class="form-group">
<label>12th Marks (%)</label>
<input type="number" name="marks">
</div>

<div class="form-group">
<label>City</label>
<input type="text" name="city">
</div>

<div class="form-group">
<label>State</label>
<input type="text" name="state">
</div>

<div class="form-group">
<label>Address</label>
<textarea name="address"></textarea>
</div>

<button type="submit">Submit</button>

</form>
</div>

</body>
</html>