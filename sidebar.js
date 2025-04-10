fetch('sidebar.html')
.then(response => {
    if (!response.ok) {
        throw new Error('Failed to load sidebar.html');
    }
    return response.text();
})
.then(data => {
    document.getElementById('sidebar-container').innerHTML = data;

    const currentPage = window.location.pathname.split('/').pop().replace('.html', '');
    const menuItems = document.querySelectorAll('.menu-item');

    if (currentPage === '') {
        const dashboardItem = document.querySelector('.menu-item[data-page="dashboard"]');
        if (dashboardItem) {
            dashboardItem.classList.add('active');
        }
    }

    menuItems.forEach(item => {
        const pageName = item.getAttribute('data-page');
        if (pageName === currentPage) {
            item.classList.add('active');
        }

        item.addEventListener('click', function() {
            menuItems.forEach(i => i.classList.remove('active'));
            this.classList.add('active');
            sessionStorage.setItem('activeMenuItem', this.getAttribute('data-page'));
        });
    });

    const activeItem = sessionStorage.getItem('activeMenuItem');
    if (activeItem) {
        const activeElement = document.querySelector(`.menu-item[data-page="${activeItem}"]`);
        if (activeElement) {
            menuItems.forEach(item => item.classList.remove('active'));
            activeElement.classList.add('active');
        }
    }
})
.catch(error => console.error('Error:', error));

const mobileMenuButton = document.querySelector('.mobile-menu-button');
const sidebar = document.querySelector('.sidebar');
const backdrop = document.querySelector('.sidebar-backdrop');

mobileMenuButton.addEventListener('click', () => {
sidebar.classList.toggle('active');
backdrop.classList.toggle('active');
});

backdrop.addEventListener('click', () => {
sidebar.classList.remove('active');
backdrop.classList.remove('active');
});
