function loadHeader(){
    fetch('../../../components/header.html')  
    .then(response => response.text())
    .then(data => {
        document.querySelector('.main-header').innerHTML = data;
    });
}

function loadSideBar(){
    fetch('../../../components/sidebar.html')  
    .then(response => response.text())
    .then(data => {
        document.querySelector('.primary-sidebar').innerHTML = data;
    });
}

/*document.addEventListener('DOMContentLoaded', () => {
    loadHeader();
    loadSideBar();
})*/