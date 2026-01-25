
document.addEventListener("DOMContentLoaded", () => {

    const questions = document.querySelectorAll(".question");

    questions.forEach(q => {
        const btn = q.querySelector(".question-button");
        const answer = q.querySelector(".answer");
        const icon = q.querySelector(".plus-icon");

        btn.addEventListener("click", () => {
            const isOpen = answer.style.maxHeight && answer.style.maxHeight !== "0px";

            if (!isOpen) {
                // Open answer
                answer.style.maxHeight = answer.scrollHeight + "px";
                answer.style.paddingTop = "10px";
                answer.style.paddingBottom = "15px";
                icon.textContent = "-";
            } else {
                // Close answer
                answer.style.maxHeight = "0";
                answer.style.paddingTop = "0";
                answer.style.paddingBottom = "0";
                icon.textContent = "+";
            }
        });
    });

    // Active nav-link highlight
    const currentPage = window.location.pathname.split("/").pop();
    const links = document.querySelectorAll(".nav-link");

    links.forEach(link => {
        if (link.getAttribute("href") === currentPage) {
            link.classList.add("active");
        }
    });
});

