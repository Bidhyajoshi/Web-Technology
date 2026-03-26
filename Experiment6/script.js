function showTab(tab){
    document.querySelectorAll(".form").forEach(f=>f.classList.remove("active"));
    document.getElementById(tab).classList.add("active");

    document.querySelectorAll(".tabs button").forEach(b=>b.classList.remove("active"));
    document.getElementById(tab+"Tab").classList.add("active");
}

// REGISTER
function register(){
    let user={
        name: name.value,
        dob: dob.value,
        gender: gender.value,
        email: email.value,
        phone: phone.value,
        address: address.value,
        city: city.value,
        state: state.value,
        school: school.value,
        marks: marks.value,
        course: course.value,
        father: father.value,
        mother: mother.value,
        pass: password.value
    };

    if(password.value !== confirmPassword.value){
        registerMsg.innerHTML="Passwords not matching";
        return;
    }

    localStorage.setItem(user.email, JSON.stringify(user));

    showPreview(user);
}

// SHOW PREVIEW
function showPreview(user){
    let output = `
    <p><b>Name:</b> ${user.name}</p>
    <p><b>DOB:</b> ${user.dob}</p>
    <p><b>Gender:</b> ${user.gender}</p>
    <p><b>Email:</b> ${user.email}</p>
    <p><b>Phone:</b> ${user.phone}</p>
    <p><b>Address:</b> ${user.address}, ${user.city}, ${user.state}</p>
    <p><b>School:</b> ${user.school}</p>
    <p><b>Marks:</b> ${user.marks}%</p>
    <p><b>Course:</b> ${user.course}</p>
    <p><b>Father:</b> ${user.father}</p>
    <p><b>Mother:</b> ${user.mother}</p>
    `;

    document.getElementById("output").innerHTML=output;

    showTab("review");
}

// LOGIN
function login(){
    let emailVal = loginEmail.value;
    let passVal = loginPassword.value;

    let data = localStorage.getItem(emailVal);

    if(!data){
        loginMsg.innerHTML="User not found";
        return;
    }

    let user = JSON.parse(data);

    if(user.pass === passVal){
        loginMsg.innerHTML="Login Success";
        showPreview(user);
    }else{
        loginMsg.innerHTML="Wrong Password";
    }
}

// BACK
function goBack(){
    showTab("register");
}