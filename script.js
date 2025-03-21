const signUpButton = document.getElementById('signUpButton');
const signInButton = document.getElementById('signInButton');
const signInForm = document.getElementById('signIn');
const signUpForm = document.getElementById('signup');
const roleSelection = document.getElementById('roleSelection');

// Toggle between sign in and sign up forms
signUpButton.addEventListener('click', function() {
    signInForm.style.display = "none";
    signUpForm.style.display = "block";
    roleSelection.style.display = "none";
});

signInButton.addEventListener('click', function() {
    signUpForm.style.display = "none";
    roleSelection.style.display = "block"; // Show role selection first
});

function selectRole(role) {
    // Store role in session first
    fetch('store_role.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'role=' + encodeURIComponent(role)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Set hidden input value
            document.getElementById('selected_role').value = role;
            // Show login form
            document.getElementById('roleSelection').style.display = 'none';
            document.getElementById('signIn').style.display = 'block';
        } else {
            console.error('Failed to store role');
        }
    })
    .catch(error => console.error('Error:', error));
}

function validateEmail(email) {
    const allowedDomains = ['gmail.com', 'yahoo.com'];
    const domain = email.split('@')[1];
    return allowedDomains.includes(domain);
}

document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const emailInput = this.querySelector('input[type="email"]');
        const emailError = emailInput.nextElementSibling;
        
        if (!validateEmail(emailInput.value)) {
            e.preventDefault();
            emailError.textContent = 'Please use either gmail.com or yahoo.com email address';
            emailError.style.display = 'block';
            emailInput.focus();
        } else {
            emailError.textContent = '';
            emailError.style.display = 'none';
        }
    });
});

function validateName(name) {
    return /^[A-Za-z ]+$/.test(name);
}

document.querySelectorAll('input[name="fName"], input[name="lName"]').forEach(input => {
    input.addEventListener('input', function() {
        const errorSpan = this.nextElementSibling;
        if (!validateName(this.value)) {
            this.setCustomValidity('Please enter alphabets only');
            errorSpan.textContent = 'Please enter alphabets only';
            errorSpan.style.display = 'block';
        } else {
            this.setCustomValidity('');
            errorSpan.style.display = 'none';
        }
    });
});

