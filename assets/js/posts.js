document.addEventListener('DOMContentLoaded', function() {
  // Select elements
  const categoryCards = document.querySelectorAll(".category-card");
  const posts = document.querySelectorAll(".post-card");
  const title = document.querySelector("#posts-title");

  // Filter posts when clicking a category
  categoryCards.forEach(card => {
    card.addEventListener("click", () => {
      const selectedCategory = card.dataset.category;

      // Update title
      if (title) {
        title.textContent = `${selectedCategory}`;
      }

      // Remove border from all cards
      categoryCards.forEach(c => {
        c.style.border = "2px solid transparent"; 
      });

      // Add border to selected card
      card.style.border = "2px solid var(--clr-botn)"; 

      // Show only the posts of that category
      posts.forEach(post => {
        if(post.dataset.category === selectedCategory){
          post.style.display = "flex";
        }
        else{
          post.style.display = "none";
        }
      });
    });
  });

  const showAllBtn = document.querySelector(".show-all-btn");

  if (showAllBtn && title) {
    showAllBtn.addEventListener("click", () => {
      title.textContent = "Browse All Posts";

      categoryCards.forEach(c => {
        c.style.border = "2px solid transparent";
      });

      posts.forEach(post => {
        post.style.display = "flex";
      });
    });
  }

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