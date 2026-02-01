$(document).ready(function () {

    $("#upload-picture-desktop, #upload-picture-mobile").on("change", function () {
        const file = this.files[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                alert("File is too large! Max 2MB.");
                $(this).val('');
                return;
            }
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#profile-preview').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    $(".remove-btn").on("click", function (e) {
        e.preventDefault();
        $('#profile-preview').attr('src', '../../assets/icons/favicon_io/android-chrome-512x512.png');
        $("#upload-picture-desktop, #upload-picture-mobile").val('');
    });

    $(document).on("change", "#offering-category", function () {
        const selectedCat = $(this).val();
        const $skillSelect = $('#offering-skills');
        const $rateInput = $('#offering-rate');
        const $addButton = $('#add-offering-skill-btn');

        if (selectedCat !== "") {
            $skillSelect.prop("disabled", false);
            $skillSelect.find("option").each(function () {
                if ($(this).data("category") == selectedCat || $(this).val() === "") {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        } else {
            $skillSelect.prop("disabled", true).val("");
            $rateInput.prop("disabled", true).val("");
            $addButton.prop("disabled", true);
        }
    });

    $(document).on("change", "#offering-skills", function () {
        const $rateInput = $('#offering-rate');
        const $addButton = $('#add-offering-skill-btn');
        if ($(this).val() !== "") {
            $rateInput.prop("disabled", false);
            $addButton.prop("disabled", false);
        } else {
            $rateInput.prop("disabled", true);
            $addButton.prop("disabled", true);
        }
    });

    $(document).on("change", "#seeking-category", function () {
        const selectedCat = $(this).val();
        const $skillSelect = $('#seeking-skills');
        const $addButton = $('#add-seeking-skill-btn');

        $skillSelect.val("");
        $addButton.prop("disabled", true);

        if (selectedCat !== "") {
            $skillSelect.prop("disabled", false);
            $skillSelect.find("option").each(function () {
                if ($(this).data("category") == selectedCat || $(this).val() === "") {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        } else {
            $skillSelect.prop("disabled", true);
        }
    });

    $(document).on("change", "#seeking-skills", function () {
        $('#add-seeking-skill-btn').prop("disabled", $(this).val() === "");
    });

    $("#add-offering-skill-btn").on("click", function () {
        const skillSelect = $("#offering-skills");
        const skillId = skillSelect.val();
        const skillName = skillSelect.find("option:selected").text();
        const proficiency = $(".proficiency-section .proficiency-slider").last().val();
        const rate = $("#offering-rate").val();

        if (!skillId || !rate) {
            alert("Please select a skill and enter a rate!");
            return;
        }

        const newCardHtml = `
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">${skillName}</h3>
                    <button type="button" class="delete-skill">×</button>
                </div>
                <div class="proficiency-section">
                    <div class="section-header">
                        <span class="label">Proficiency Level</span>
                        <span class="proficiency-value">${proficiency}%</span>
                    </div>
                    <div class="slider-container">
                        <input type="range" class="proficiency-slider" name="skill_proficiency[${skillId}]" min="0" max="100" value="${proficiency}" oninput="updateSlider(this)">
                    </div>
                </div>
                <div class="rate-section">
                    <div class="label"><label>Rate (credits/hour)*</label></div>
                    <input type="number" name="skill_rate[${skillId}]" class="textbox" value="${rate}" required>
                </div>
            </div>`;

        $(".cards-section").append(newCardHtml);
        updateSlider($(".cards-section .proficiency-slider").last()[0]);

        $("#offering-category").val("");
        skillSelect.val("").prop("disabled", true);
        $("#offering-rate").val("").prop("disabled", true);
        $(this).prop("disabled", true);
    });

    $("#add-seeking-skill-btn").on("click", function () {
        const skillId = $("#seeking-skills").val();
        const skillName = $("#seeking-skills option:selected").text();

        if ($("#seeking-list li").length >= 10) {
            alert("Max 10 skills allowed!");
            return;
        }

        const listItem = `
            <li>
                ${skillName}
                <input type="hidden" name="seeking_skills[]" value="${skillId}">
                <span class="delete-from-list">&times;</span>
            </li>`;

        $("#seeking-list").append(listItem);
        updateSkillCounter();

        $("#seeking-category").val("");
        $("#seeking-skills").val("").prop("disabled", true);
        $(this).prop("disabled", true);
    });

    $(document).on("click", ".delete-skill", function () {
        if (confirm("Remove this offering skill?")) $(this).closest(".card").remove();
    });

    $(document).on("click", ".delete-from-list", function () {
        $(this).parent().remove();
        updateSkillCounter();
    });

    $("#update-profile-form").on("submit", function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        const saveBtn = $(".btn-2-save");
        saveBtn.text("Saving...").prop("disabled", true);

        $.ajax({
            url: "update_handler.php",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    window.location.reload();
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function () {
                alert("Could not connect to server.");
            },
            complete: function () {
                saveBtn.text("Save Changes").prop("disabled", false);
            }
        });
    });
    function updateSkillCounter() {
        const count = $("#seeking-list li").length;
        $("#skill-counter").text(count + "/10 Skills selected");
    }

    updateSkillCounter();
    document.querySelectorAll('.proficiency-slider').forEach(s => updateSlider(s));
});

function updateSlider(input) {
    if (!input) return;
    const value = input.value;
    const card = input.closest('.proficiency-section');
    const display = card.querySelector('.proficiency-value');
    if (display) display.textContent = value + '%';
    input.style.background = `linear-gradient(90deg, #ffa36c ${value}%, #47557a ${value}%)`;
}

function updateSkillCounter() {
    const count = $("#seeking-list li").length;
    $("#skill-counter").text(`${count}/10 Skills selected`);
}

let currentName = "";

window.onload = function() {
    currentName = document.getElementById("username").value;
};

function checkUser(username) {
    if (username.length < 3) {
        document.getElementById("username-status").innerHTML = "";
        return;
    }

    const pattern = /^(?![_.])(?!.*[_.]{2})[a-z0-9._]{3,20}(?<![_.])$/;

    if (!pattern.test(username)) {
        document.getElementById("username-status").innerHTML = "Invalid format";
        document.getElementById("username-status").style.color = "orange";
        return; 
    }

    if (username === currentName) {
        document.getElementById("username-status").innerHTML = "";
        return; 
    }

    let xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function(){

        if(this.readyState == 4 && this.status ==200) {
            let msg = this.responseText;
            let statusSpan = document.getElementById("username-status");

            statusSpan.innerHTML = msg;
            statusSpan.style.color = (msg === 'username is available') ? '#00ff00' : 'red';
        }
    };
    xhttp.open("GET", "../../dashboard/profile/check_user.php?username="+username, true);
    xhttp.send();
}