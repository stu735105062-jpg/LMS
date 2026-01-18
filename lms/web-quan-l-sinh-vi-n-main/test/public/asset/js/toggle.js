// Light and dark mode js
const theme = localStorage.getItem('theme') || 'light';
applyTheme(theme);

// Function to toggle and save theme
function myFunction() {
    const newTheme = (document.body.dataset.bsTheme === 'light') ? 'dark' : 'light';
    document.body.dataset.bsTheme = newTheme;
    applyTheme(newTheme);
    localStorage.setItem('theme', newTheme);
}

// Apply gradient background and text color
function applyTheme(theme) {
    const body = document.body;
    body.dataset.bsTheme = theme;
    body.style.transition = "background 1s ease, color 1s ease";

    if (theme === 'dark') {
        body.style.background = "linear-gradient(135deg, #000000, #0d1a14, #001122)";
        body.style.color = "#f1f1f1";
    } else {
        body.style.background = "linear-gradient(135deg, #ffffff 90%, #f0fff4 95%, #e6f0ff 97%, #fff5f5 99%)";
        body.style.color = "#121212";
    }
}