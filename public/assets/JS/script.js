// toggle header
const toggleBtn = document.querySelector(".toggle-btn");
const innerList = document.querySelector(".navbar-inner-list");

toggleBtn.addEventListener("click", () => {
  innerList.classList.toggle("show");
});
