const toggleButton = document.querySelector('.dropdown-toggle');
const dropdownheader = document.querySelector('.header-dropdown');


toggleButton.addEventListener('click', function (event) {
    event.stopPropagation(); // Prevent click from propagating to the document
    dropdownheader.style.display = dropdownheader.style.display === 'block' ? 'none' : 'block';
});

document.addEventListener('click', function () {
    if (dropdownheader.style.display === 'block') {
        dropdownheader.style.display = 'none';
    }
});
