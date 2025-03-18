let sidebar = document.querySelector(".sidebar");
let closeBtn = document.querySelector("#btn");
let searchBtn = document.querySelector(".bx-search");
let body = document.querySelector("body");
let navList = document.querySelector(".nav-list");

if (window.innerWidth < 767) {
  sidebar.classList.toggle("open");
  navList.classList.toggle("scroll");
  body.classList.toggle("sidebar-closed");
  menuBtnChange();
}

closeBtn.addEventListener("click", () => {
  sidebar.classList.toggle("open");
  navList.classList.toggle("scroll");
  body.classList.toggle("sidebar-opened");
  body.classList.toggle("sidebar-closed");
  menuBtnChange();
});

if(searchBtn){
  searchBtn.addEventListener("click", () => {
    sidebar.classList.toggle("open");
    navList.classList.toggle("scroll");
    body.classList.toggle("sidebar-opened");
    menuBtnChange();
  });
}

function menuBtnChange() {
  if (sidebar.classList.contains("open")) {
    closeBtn.classList.replace("bx-menu", "bx-menu-alt-right");
  } else {
    closeBtn.classList.replace("bx-menu-alt-right", "bx-menu");
  }
}

document.addEventListener('DOMContentLoaded', () => {
    const links = document.querySelectorAll('.sidebar a');
    links.forEach(link => {
        if (link.href === window.location.href) {
            link.classList.add('active');
        }
    });
});
