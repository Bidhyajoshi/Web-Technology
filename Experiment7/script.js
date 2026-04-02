// Student Data
let students = [
    {name: "Amit", marks: 95, dept: "CSE"},
    {name: "Riya", marks: 88, dept: "IT"},
    {name: "Karan", marks: 92, dept: "CSE"},
    {name: "Sneha", marks: 75, dept: "ECE"}
];

// Product Data
let products = [
    {name: "Laptop", category: "electronics", price: 50000},
    {name: "Chair", category: "furniture", price: 2000},
    {name: "Rice", category: "groceries", price: 800},
    {name: "Mobile", category: "electronics", price: 20000}
];

// Event Handling (Button Click)
document.getElementById("studentBtn").onclick = function () {

    let option = document.getElementById("studentSelect").value;
    let search = document.getElementById("searchBox").value.toLowerCase();

    let result = students;

    if (search !== "") {
        result = result.filter(s => s.name.toLowerCase().includes(search));
    }

    if (option === "top") {
        let max = Math.max(...students.map(s => s.marks));
        result = students.filter(s => s.marks === max);
    }
    else if (option === "90") {
        result = students.filter(s => s.marks > 90);
    }
    else if (option === "cse") {
        result = students.filter(s => s.dept === "CSE");
    }

    document.getElementById("studentResult").innerHTML =
        result.map(s => `${s.name} (${s.marks})`).join("<br>");
};


// Product Event
document.getElementById("productBtn").onclick = function () {

    let option = document.getElementById("productSelect").value;
    let result = products;

    if (option === "electronics") {
        result = products.filter(p => p.category === "electronics");
    }
    else if (option === "furniture") {
        result = products.filter(p => p.category === "furniture");
    }
    else if (option === "groceries") {
        result = products.filter(p => p.category === "groceries");
    }
    else if (option === "total") {
        let total = products.reduce((sum, p) => sum + p.price, 0);
        document.getElementById("productResult").innerHTML =
            "Total Price: ₹" + total;
        return;
    }

    document.getElementById("productResult").innerHTML =
        result.map(p => `${p.name} - ₹${p.price}`).join("<br>");
};