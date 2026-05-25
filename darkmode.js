function toggleDarkMode() {
    const body = document.body;
    body.classList.toggle('dark-mode');
    
    // Save preference to localStorage
    const isDarkMode = body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDarkMode ? 'enabled' : 'disabled');
    
    // Update toggle button icons
    const moonIcon = document.querySelector('.moon-icon');
    const sunIcon = document.querySelector('.sun-icon');
    
    if (moonIcon && sunIcon) {
        if (isDarkMode) {
            // Dark mode = show moon
            moonIcon.style.display = 'inline';
            sunIcon.style.display = 'none';
        } else {
            // Light mode = show sun
            moonIcon.style.display = 'none';
            sunIcon.style.display = 'inline';
        }
    }
}

// Check for saved preference on page load
document.addEventListener('DOMContentLoaded', function() {
    const darkMode = localStorage.getItem('darkMode');
    const body = document.body;
    const moonIcon = document.querySelector('.moon-icon');
    const sunIcon = document.querySelector('.sun-icon');
    
    if (darkMode === 'enabled') {
        body.classList.add('dark-mode');
        if (moonIcon && sunIcon) {
            moonIcon.style.display = 'inline';
            sunIcon.style.display = 'none';
        }
    } else {
        // Light mode by default
        if (moonIcon && sunIcon) {
            moonIcon.style.display = 'none';
            sunIcon.style.display = 'inline';
        }
    }
});