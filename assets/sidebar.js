import './styles/sidebar.css';

const sidebar = document.getElementById('homepage-sidebar');
const homepage = document.querySelector('.Homepage');
if (sidebar && homepage) {
    sidebar.addEventListener('turbo:frame-render', (e) => {
        const url = new URL(e.detail.fetchResponse?.response?.url);
        if ('/' !== url.pathname) {
            homepage.classList.add('Homepage--sidebarOpen');
        } else {
            homepage.classList.remove('Homepage--sidebarOpen');
        }
    });
}
