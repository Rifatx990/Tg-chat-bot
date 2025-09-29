<?php
// index.php
include "config.php";

// Get admin_id (for authentication in AJAX requests)
$admin_id = $_GET['admin_id'] ?? '';
if (!in_array($admin_id, $admins)) {
    die("Unauthorized access");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            height: 100vh;
            margin: 0;
        }
        #userList {
            width: 250px;
            border-right: 1px solid #ccc;
            overflow-y: auto;
            background: #f4f4f4;
            padding: 10px;
        }
        #chatArea {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        #messages {
            flex: 1;
            padding: 10px;
            overflow-y: auto;
            border-bottom: 1px solid #ccc;
        }
        #inputArea {
            display: flex;
            padding: 10px;
        }
        #inputArea input {
            flex: 1;
            padding: 10px;
            font-size: 16px;
        }
        #inputArea button {
            padding: 10px;
        }
        .user {
            padding: 8px;
            cursor: pointer;
            border-bottom: 1px solid #ddd;
        }
        .user:hover {
            background: #ddd;
        }
        .active {
            background: #bbb;
        }
    </style>
</head>
<body>
    <div id="userList"></div>
    <div id="chatArea">
        <div id="messages">Select a user to view messages</div>
        <div id="inputArea">
            <input type="text" id="messageInput" placeholder="Type a message...">
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>

<script>
let currentChatId = null;
let adminId = "<?php echo $admin_id; ?>";

// Load user list automatically every 5s
function loadUsers() {
    fetch("get_users.php?admin_id=" + adminId)
        .then(res => res.json())
        .then(data => {
            let userList = document.getElementById("userList");
            userList.innerHTML = "";
            data.forEach(user => {
                let div = document.createElement("div");
                div.className = "user" + (user.chat_id === currentChatId ? " active" : "");
                div.innerText = user.name;
                div.onclick = () => {
                    currentChatId = user.chat_id;
                    document.querySelectorAll(".user").forEach(u => u.classList.remove("active"));
                    div.classList.add("active");
                    loadMessages();
                };
                userList.appendChild(div);
            });
        });
}

// Load messages for the selected user
function loadMessages() {
    if (!currentChatId) return;
    fetch("get_chat.php?admin_id=" + adminId + "&chat_id=" + currentChatId)
        .then(res => res.json())
        .then(data => {
            let msgArea = document.getElementById("messages");
            msgArea.innerHTML = "";
            data.forEach(msg => {
                let p = document.createElement("p");
                p.innerHTML = "<b>" + msg.sender + ":</b> " + msg.text;
                msgArea.appendChild(p);
            });
            msgArea.scrollTop = msgArea.scrollHeight;
        });
}

// Send a message
function sendMessage() {
    let text = document.getElementById("messageInput").value.trim();
    if (!text || !currentChatId) return;
    fetch("send_message.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "admin_id=" + adminId + "&chat_id=" + currentChatId + "&message=" + encodeURIComponent(text)
    }).then(() => {
        document.getElementById("messageInput").value = "";
        loadMessages();
    });
}

// Auto-refresh user list + chat messages
setInterval(() => {
    loadUsers();
    loadMessages();
}, 5000);

// Initial load
loadUsers();
</script>
</body>
</html>
