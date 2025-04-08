<?php
include "layout/header.php";

// Redirect if not logged in
if (!isset($_SESSION["id"])) {
    echo "<script>alert('Session expired. Please log in again.'); window.location.href = '/login.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Money Transfer</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <!-- User Search -->
    <h3>User Search</h3>
    <input type="text" id="searchUser" placeholder="Search by username or ID" class="form-control mb-3">
    <ul id="userList" class="list-group"></ul>

    <!-- Money Transfer -->
    <h3 class="mt-5">Money Transfer</h3>
    <form id="transferForm">
        <input type="number" id="receiverId" placeholder="Receiver ID" class="form-control mb-2" required>
        <input type="number" id="amount" placeholder="Amount" class="form-control mb-2" required>
        <input type="text" id="comment" placeholder="Comment (Optional)" class="form-control mb-2">
        <button type="submit" class="btn btn-primary">Transfer</button>
    </form>

    <!-- Transaction History -->
    <h3 class="mt-5">Transaction History</h3>
    <input type="number" id="historyUserId" placeholder="Enter User ID" class="form-control mb-2">
    <button onclick="getTransactionHistory()" class="btn btn-secondary">Get History</button>
    <ul id="transactionList" class="list-group mt-3"></ul>
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

            console.log(result);

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


    // Money Transfer
    document.getElementById('transferForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const receiverId = document.getElementById('receiverId').value.trim();
        const amount = document.getElementById('amount').value.trim();
        const comment = document.getElementById('comment').value.trim();

        if (!receiverId || !amount) {
            alert('Please enter valid receiver ID and amount');
            return;
        }

        try {
            const response = await fetch('./transfer_money.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `receiver_id=${encodeURIComponent(receiverId)}&amount=${encodeURIComponent(amount)}&comment=${encodeURIComponent(comment)}`
            });

            const result = await response.text();
            if (response.ok) {
                alert(result);
                document.getElementById('transferForm').reset();
            } else {
                throw new Error(result);
            }
        } catch (error) {
            console.error('Error during transfer:', error);
            alert(`Transfer failed: ${error.message}`);
        }
    });

    // Get Transaction History
    async function getTransactionHistory() {
        const userId = document.getElementById('historyUserId').value.trim();

        if (!userId) {
            alert('Please enter a valid user ID');
            return;
        }

        try {
            const response = await fetch(`./transaction_history.php?id=${encodeURIComponent(userId)}`);
            if (!response.ok) throw new Error(`Error: ${response.statusText}`);

            const transactions = await response.json();
            const transactionList = document.getElementById('transactionList');
            transactionList.innerHTML = '';

            if (transactions.length === 0) {
                transactionList.innerHTML = `<li class="list-group-item text-warning">No transactions found</li>`;
                return;
            }

            transactions.forEach(tx => {
                transactionList.innerHTML += `<li class="list-group-item">
                    To: ${tx.receiver_id} | Amount: $${tx.amount} | Comment: ${tx.comment || 'No comment'} | Date: ${tx.created_at}
                </li>`;
            });
        } catch (error) {
            console.error('Error fetching transaction history:', error);
            alert('Failed to fetch transaction history');
        }
    }
</script>

</body>
</html>

<?php
include "layout/footer.php";
?>