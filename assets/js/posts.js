document.addEventListener('DOMContentLoaded', function() {
  // Category filtering is done only in posts.php (links to posts.php?category=...)

  const searchInput = document.querySelector(".search-input");
  const allPosts = document.querySelectorAll(".post-card");
  const searchBtn = document.querySelector(".search-btn");

  function runSearch() {
    const query = searchInput.value.toLowerCase();
    allPosts.forEach(function(post) {

      // Get title text
      const postTitle = post.querySelector(".post-field");
      const title = postTitle ? postTitle.textContent.toLowerCase() : "";

      // Get description text
      const postDesc = post.querySelector(".post-description");
      const description = postDesc ? postDesc.textContent.toLowerCase() : "";

      // Get all skills text
      let skillsText = "";
      const skillItems = post.querySelectorAll(".skills span");
      skillItems.forEach(function(skill) {
        skillsText += skill.textContent.toLowerCase() + " ";
      });

      // Check if search matches ANY field
      if (
        title.includes(query) ||
        description.includes(query) ||
        skillsText.includes(query)
      ) {
        post.style.display = "flex";
      } else {
        post.style.display = "none";
      }
    });
  }

  if (searchBtn) {
    searchBtn.addEventListener("click", runSearch);
  }

  if (searchInput) {
    searchInput.addEventListener("keydown", function(event) {
      if (event.key === "Enter") {
        runSearch();
      }
    });
  }
});
document.querySelectorAll('.like-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    btn.classList.toggle('liked');
  });
});