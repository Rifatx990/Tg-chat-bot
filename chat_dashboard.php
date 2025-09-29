<?php
include "config.php";

// Check admin access
$admin_id = $_GET['admin_id'] ?? '';
if(!in_array($admin_id, $admins)){
    echo "<h3>Access Denied: You are not an admin</h3>";
    exit;
}

// Load chat data
$chats = loadJson($chatsFile); // messages stored in JSON

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Telegram Chat Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.chat-box { max-height:400px; overflow-y:auto; border:1px solid #ccc; padding:10px; margin-bottom:10px; background:#f9f9f9; border-radius:10px;}
.msg-user { background:#e2f0ff; padding:5px 10px; border-radius:15px; margin:5px 0; display:flex; align-items:center; }
.msg-admin { background:#d1ffd7; padding:5px 10px; border-radius:15px; margin:5px 0; display:flex; justify-content:flex-end; align-items:center;}
.msg img { width:30px; height:30px; border-radius:50%; margin-right:10px; }
.msg-admin img { margin-left:10px; margin-right:0; }
.clearfix::after { content:""; clear:both; display:table; }
</style>
</head>
<body>
<div class="container my-4">
<h3>Telegram Chat Dashboard</h3>

<div class="mb-2">
<label>Select User:</label>
<select id="userSelect" class="form-control"></select>
</div>

<div id="chatContainer" class="mb-2"></div>

<div class="mb-2">
<textarea id="adminMessage" class="form-control" placeholder="Type message"></textarea>
<input type="file" id="adminMedia" class="form-control mt-1">
<button class="btn btn-primary mt-1" id="sendBtn">Send</button>
</div>

<div class="card mt-3">
<div class="card-body">
<h5>Admin Settings</h5>
<label>Auto-delete messages older than (days):</label>
<input type="number" id="autoDeleteDays" class="form-control" min="1">
<button class="btn btn-warning mt-2" id="saveSettingsBtn">Save Settings</button>
<button class="btn btn-danger mt-2" id="cleanNowBtn">Clean Now</button>
</div>
</div>
</div>

<script>
let chats = {};
const admin_id = "<?= $admin_id ?>";

// Load chats and populate user select
async function loadChats(){
    const res = await fetch('get_chat.php?admin_id='+admin_id);
    chats = await res.json();
    const userSelect = document.getElementById('userSelect');
    const prev = userSelect.value;
    userSelect.innerHTML = '';
    Object.keys(chats).forEach(chat_id => {
        const userName = chats[chat_id].name || chat_id;
        userSelect.innerHTML += `<option value="${chat_id}">${userName}</option>`;
    });
    displayChat(prev || userSelect.value);
}

// Display chat for selected user with profile pics & media
function displayChat(chat_id){
    const container = document.getElementById('chatContainer');
    container.innerHTML = '';
    if(!chat_id || !chats[chat_id]) return;
    const messages = chats[chat_id].messages;
    const chatBox = document.createElement('div');
    chatBox.className = 'chat-box';
    messages.forEach(msg => {
        const div = document.createElement('div');
        div.className = msg.from=='user' ? 'msg-user clearfix msg' : 'msg-admin clearfix msg';
        const pic = msg.profile_pic ? `<img src="${msg.profile_pic}" alt="User Pic">` : '';
        let mediaHtml = '';
        if(msg.file_url){
            const ext = msg.file_url.split('.').pop().toLowerCase();
            if(['png','jpg','jpeg','gif','webp'].includes(ext)){
                mediaHtml = `<div><img src="${msg.file_url}" style="max-width:100%;margin-top:5px;border-radius:10px;"></div>`;
            } else {
                mediaHtml = `<div><a href="${msg.file_url}" target="_blank">Download File</a></div>`;
            }
        }
        div.innerHTML = msg.from=='user'
            ? `${pic}<b>${msg.name}:</b> ${msg.message || ''} ${mediaHtml}`
            : `<b>Admin:</b> ${msg.message || ''} ${pic} ${mediaHtml}`;
        chatBox.appendChild(div);
    });
    container.appendChild(chatBox);
    container.scrollTop = container.scrollHeight;
}

// When user selection changes
document.getElementById('userSelect').addEventListener('change', e=>{
    displayChat(e.target.value);
});

// Send message (text + media)
document.getElementById('sendBtn').addEventListener('click', async ()=>{
    const msg = document.getElementById('adminMessage').value.trim();
    const fileInput = document.getElementById('adminMedia');
    const file = fileInput.files[0] || null;
    const chat_id = document.getElementById('userSelect').value;
    if(!msg && !file) return alert('Type a message or select a file');
    const formData = new FormData();
    formData.append('admin_id', admin_id);
    formData.append('chat_id', chat_id);
    formData.append('message', msg);
    if(file) formData.append('file', file);
    await fetch('chat_send.php',{method:'POST',body:formData});
    document.getElementById('adminMessage').value = '';
    fileInput.value = '';
    loadChats();
});

// Load settings
async function loadSettings() {
    const res = await fetch('chat_settings_api.php?admin_id=' + admin_id);
    const data = await res.json();
    document.getElementById('autoDeleteDays').value = data.auto_delete_days || 1;
}

// Save settings
document.getElementById('saveSettingsBtn').addEventListener('click', async ()=>{
    const days = parseInt(document.getElementById('autoDeleteDays').value);
    await fetch(`chat_settings_api.php?admin_id=${admin_id}&auto_delete_days=${days}`);
    alert('Settings saved!');
});

// Clean old chats manually
document.getElementById('cleanNowBtn').addEventListener('click', async ()=>{
    await fetch('clean_chat.php?admin_id=' + admin_id);
    alert('Old messages cleaned!');
    loadChats();
});

// Auto-refresh every 5s
setInterval(loadChats,5000);
loadChats();
loadSettings();
</script>
</body>
</html>
