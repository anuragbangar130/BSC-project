// ================= SEMESTER DATA =================

const semesterSubjects = {

    1: [
        "Programming Fundamentals",
        "Computer Fundamentals",
        "Mathematics",
        "Digital Electronics"
    ],

    2: [
        "Data Structures",
        "Database Management System",
        "Computer Networks",
        "Object Oriented Programming"
    ],

    3: [
        "Core Java",
        "Operating System",
        "Data Science",
        "Theory of Computation"
    ],

    4: [
        "Web Technology",
        "Software Engineering",
        "Computer Graphics",
        "Python Programming"
    ],

    5: [
        "Data Science",
        "Operating System",
        "Core Java",
        "Theory of Computation",
        "Frontend Fundamentals",
        "Database Management"
    ],

    6: [
        "Artificial Intelligence",
        "Machine Learning",
        "Cloud Computing",
        "Cyber Security",
        "Project"
    ]

};


// ================= SHOW SEMESTER =================

function showSemester(semester) {

    const title = document.getElementById("semesterTitle");
    const container = document.getElementById("subjectContainer");

    if (!title || !container) {
        console.error("Semester title or subject container not found.");
        return;
    }

    title.innerText = "Semester " + semester + " Subjects";
    container.innerHTML = "";

    const subjects = semesterSubjects[semester];

    if (!subjects) {
        container.innerHTML = "<p>No subjects available.</p>";
        return;
    }

    subjects.forEach(function(subject) {

        const card = document.createElement("div");
        card.className = "subject-card";

        const icon = document.createElement("div");
        icon.className = "card-icon";
        icon.textContent = "📚";

        const heading = document.createElement("h3");
        heading.textContent = subject;

        const description = document.createElement("p");
        description.textContent =
            "Explore important concepts and useful learning information related to " +
            subject + ".";

        const button = document.createElement("button");
        button.textContent = "View Information";

        button.addEventListener("click", function() {
            openSubject(subject);
        });

        card.appendChild(icon);
        card.appendChild(heading);
        card.appendChild(description);
        card.appendChild(button);

        container.appendChild(card);

    });

}


// ================= OPEN SUBJECT =================

function openSubject(subject) {

    localStorage.setItem("bcsPediaSubject", subject);

    // Find the subject ID from the database.
    fetch("backend/get_subjects.php")
        .then(function(response) {
            if (!response.ok) {
                throw new Error("Unable to load subjects.");
            }
            return response.json();
        })
        .then(function(data) {

            let subjects = [];

            if (Array.isArray(data)) {
                subjects = data;
            } else if (Array.isArray(data.subjects)) {
                subjects = data.subjects;
            } else if (Array.isArray(data.results)) {
                subjects = data.results;
            }

            const matchedSubject = subjects.find(function(item) {
                return String(item.subject_name || item.title || "")
                    .trim()
                    .toLowerCase() === subject.trim().toLowerCase();
            });

            if (!matchedSubject || !matchedSubject.id) {
                alert(
                    "This subject was not found in the database. " +
                    "Please select a subject available in BCS Pedia."
                );
                return;
            }

            localStorage.setItem(
                "bcsPediaSubjectId",
                String(matchedSubject.id)
            );

            localStorage.setItem(
                "bcsPediaSubject",
                matchedSubject.subject_name || matchedSubject.title || subject
            );

            window.location.href = "subject-details.html";

        })
        .catch(function(error) {
            console.error("Subject loading error:", error);
            alert("Unable to load subject information. Please try again.");
        });

}


// ================= LOGIN =================

function openLogin() {
    window.location.href = "login.html";
}


// ================= ADD INFORMATION =================

function openAddContent() {
    window.location.href = "add_information.html";
}


// ================= SEARCH =================

function searchContent() {

    const searchInput = document.getElementById("searchInput");

    if (!searchInput) {
        return;
    }

    const searchText = searchInput.value.trim();

    if (searchText === "") {
        alert("Please enter a subject or information to search.");
        return;
    }

    window.location.href =
        "search.html?q=" + encodeURIComponent(searchText);

}