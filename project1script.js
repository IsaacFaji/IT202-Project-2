document.addEventListener("DOMContentLoaded", function () {
    const toggle = document.getElementById("togglePassword");
    const passwordField = document.getElementById("password");

    if (toggle && passwordField) {
        toggle.addEventListener("click", function () {
            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggle.textContent = "🙈";
            } else {
                passwordField.type = "password";
                toggle.textContent = "👁";
            }
        });
    }
});

function normalizePhone(phone) {
    return phone.toLowerCase().replace(/[-\s]/g, "");
}

function validate() {
    let first = document.getElementById("firstName");
    let last = document.getElementById("lastName");
    let phone = document.getElementById("phone");
    let id = document.getElementById("designerID");
    let email = document.getElementById("email");
    let password = document.getElementById("password");
    let confirmBox = document.getElementById("emailConfirm");
    let transaction = document.getElementById("transaction");

    let nameRegex = /^[A-Za-z]+$/;
    let idRegex = /^\d{3}$/;
    let phoneRegex = /^\d{3}[- ]?\d{3}[- ]?\d{4}\s?ext\s?\d+$/i;
    let emailRegex = /^[^\s@]+@[^\s@]+\.[A-Za-z]{1,4}$/;
    let passwordRegex = /^[^A-Za-z0-9](?=.*[A-Z])(?=.*\d).{1,6}$/;

    if (!nameRegex.test(first.value.trim())) {
        alert("First name must contain letters only.");
        first.focus();
        return false;
    }

    if (!nameRegex.test(last.value.trim())) {
        alert("Last name must contain letters only.");
        last.focus();
        return false;
    }

    if (!idRegex.test(id.value.trim())) {
        alert("Designer ID must be exactly 3 digits.");
        id.focus();
        return false;
    }

    if (!phoneRegex.test(phone.value.trim())) {
        alert("Phone must be in format: 123-456-7890 ext123");
        phone.focus();
        return false;
    }

    if (!passwordRegex.test(password.value.trim())) {
        alert("Password must start with a special character, contain 1 uppercase letter, contain 1 number, and be max 6 characters.");
        password.focus();
        return false;
    }

    if (confirmBox.checked) {
        if (!emailRegex.test(email.value.trim())) {
            alert("Enter a valid email address.");
            email.focus();
            return false;
        }
    }

    if (transaction.value === "") {
        alert("Please select a transaction.");
        transaction.focus();
        return false;
    }

    return true;
}