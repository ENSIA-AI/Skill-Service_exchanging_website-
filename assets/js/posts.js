// Select elements
const categoryCards = document.querySelectorAll(".category-card");
const posts = document.querySelectorAll(".post-card");
const title = document.querySelector("#posts-title");
const showAllBtn = document.querySelector("#show-all-btn");

// Filter posts when clicking a category
categoryCards.forEach(card => {
  card.addEventListener("click", () => {
    const selectedCategory = card.dataset.category;

    // Update title
    title.textContent = `${selectedCategory}`;

    // Remove border from all cards
    categoryCards.forEach(c => {
      c.style.border = "2px solid transparent"; 
    });

    // Add border to selected card
    card.style.border = "2px solid var(--clr-botn)"; 

    // Show only the posts of that category
    posts.forEach(post => {
      if(post.dataset.category === selectedCategory){
        post.style.display ="block";
      }
      else{
        post.style.display ="none";
      }
    
    });
  

    
  });
});

// Reset to show ALL posts
showAllBtn.addEventListener("click", () => {
  title.textContent = "Browse All Posts";

  // Remove border from all category cards
  categoryCards.forEach(c => {
    c.style.border = "2px solid transparent";
  });

  // Show all posts
  posts.forEach(post => {
    post.style.display = "block";
  });
});

const searchInput = document.querySelector(".search-input");
const allPosts = document.querySelectorAll(".post-card");
const searchBtn = document.querySelector(".search-btn");
function runSearch() {
  const query = searchInput.value.toLowerCase();
  allPosts.forEach(function(post) {

    // Get title text
    const title = post.querySelector(".post-field").textContent.toLowerCase();

    // Get description text
    const description = post.querySelector(".post-description").textContent.toLowerCase();

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
      post.style.display = "block";
    } else {
      post.style.display = "none";
    }
  });
}
searchBtn.addEventListener("click", runSearch);
searchInput.addEventListener("keydown", function(event) {
  if (event.key === "Enter") {
    runSearch();
  }
});

document.addEventListener("DOMContentLoaded", () => {
  const posts = document.querySelectorAll(".post-card");
  const categories = document.querySelectorAll(".category-card");

  categories.forEach(category => {
    const categoryName = category.dataset.category;
    let postCount=0;
   

    posts.forEach(post=>{
      if (post.dataset.category === categoryName) {
        postCount++;
      }
    });

    category.querySelector(".posts-available").textContent =
      postCount > 0 ? `${postCount} posts available` : "No posts available";
  });
});
