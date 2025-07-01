const signUpButton = document.getElementById('signUp');
const signInButton = document.getElementById('signIn');
const container = document.getElementById('container');

signUpButton.addEventListener('click', () => {
	container.classList.add("right-panel-active");
});

signInButton.addEventListener('click', () => {
	container.classList.remove("right-panel-active");
});

document.getElementById("signupForm").addEventListener("submit", function(e) {
    const button = document.getElementById("signupButton");
    button.disabled = true;
    button.classList.add("loading");
});

document.getElementById("loginForm").addEventListener("submit", function(e) {
    const button = document.getElementById("loginButton");
    button.disabled = true;
    button.classList.add("loading");
});

