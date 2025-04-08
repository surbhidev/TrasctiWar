<?php
include "layout/header.php";
include "tools/log_activity.php"; // Include the log activity function

// Redirect to login if the user is not authenticated
if (!isset($_SESSION["email"])) {
    header("location: /login.php");
    exit;
}

// Log user activity
logUserActivity($_SESSION["username"], '/profile.php');
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-6 mx-auto">
            <!-- Profile Details Section -->
            <div class="border shadow p-4 rounded bg-white mb-4">
                <h2 class="text-center mb-4">Profile</h2>
                <hr />

                <div class="row mb-3">
                    <div class="col-sm-4">Username</div>
                    <div class="col-sm-8"><?= htmlspecialchars($_SESSION["username"]) ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4">First Name</div>
                    <div class="col-sm-8"><?= htmlspecialchars($_SESSION["first_name"]) ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4">Last Name</div>
                    <div class="col-sm-8"><?= htmlspecialchars($_SESSION["last_name"]) ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4">Email</div>
                    <div class="col-sm-8"><?= htmlspecialchars($_SESSION["email"]) ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4">Phone</div>
                    <div class="col-sm-8"><?= htmlspecialchars($_SESSION["phone"]) ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4">Registered At</div>
                    <div class="col-sm-8"><?= htmlspecialchars($_SESSION["created_at"]) ?></div>
                </div>
            </div>

            <!-- Money Transfer and Edit Profile Section -->
            <div class="border shadow p-4 rounded bg-white">
                <h3 class="text-center mb-4">Actions</h3>
                <hr />

                <div class="d-grid gap-2">
                    <a href="/money_transfer.php" class="btn btn-primary">Money Transfer</a>
                    <a href="/profile_edit.php" class="btn btn-secondary">Edit Profile</a>
                </div>
            </div>

            <!-- Search Users Section -->
            <div class="border shadow p-4 rounded bg-white mt-4">
                <h3 class="text-center mb-4">Search Users</h3>
                <hr />

                <input type="text" id="searchUser" placeholder="Search by username or ID" class="form-control mb-3">
                <ul id="userList" class="list-group"></ul>
            </div>
        </div>
    </div>
</div>

<script>
    // Search users by username or ID
    let debounceTimer;
    document.getElementById('searchUser').addEventListener('input', (e) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            searchUsers(e.target.value.trim());
        }, 300); // 300ms delay
    });

    async function searchUsers(query) {
        if (query.length >= 1) {
            try {
                const response = await fetch(`/search_user.php?term=${encodeURIComponent(query)}`);
                if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

                const result = await response.json();

                console.log("Search Results:", result); // Debugging: Log the search results

                const userList = document.getElementById('userList');
                userList.innerHTML = '';

                if (result.success && result.users.length > 0) {
                    result.users.forEach(user => {
                        const listItem = document.createElement('li');
                        listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                        listItem.innerHTML = `
                            <span>${user.id} - ${user.username.replace(/</g, "&lt;").replace(/>/g, "&gt;")}</span>
                            <a href="/view_profile.php?id=${user.id}" class="btn btn-sm btn-primary">View Profile</a>
                        `;
                        userList.appendChild(listItem);
                    });
                } else {
                    userList.innerHTML = `<li class="list-group-item text-warning">No users found</li>`;
                }
            } catch (error) {
                console.error('Error fetching users:', error);
                alert(`Failed to fetch users: ${error.message}`);
            }
        } else {
            document.getElementById('userList').innerHTML = '';
        }
    }
</script>

<?php
include "layout/footer.php";
?>