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
<input type="hidden" id="admin_id" value="6631601772"> <!-- admin IDs -->

<div class="mb-2">
<label>Select User:</label>
<select id="userSelect" class="form-control"></select>
</div>

<div id="chatContainer" class="mb-2"></div>

<div class="mb-2">
<textarea id="adminMessage" class="form-control" placeholder="Type message"></textarea>
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

// Load chats and populate user select
async function loadChats(){
    const res = await fetch('get_chat.php');
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

// Display chat for selected user with profile pics
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
        div.innerHTML = msg.from=='user'
            ? `${pic}<b>${msg.name}:</b> ${msg.message}`
            : `<b>Admin:</b> ${msg.message} ${pic}`;
        chatBox.appendChild(div);
    });
    container.appendChild(chatBox);
    container.scrollTop = container.scrollHeight;
}

// When user selection changes
document.getElementById('userSelect').addEventListener('change', e=>{
    displayChat(e.target.value);
});

// Send message to selected user
document.getElementById('sendBtn').addEventListener('click', async ()=>{
    const msg = document.getElementById('adminMessage').value.trim();
    const admin_id = document.getElementById('admin_id').value.trim();
    const chat_id = document.getElementById('userSelect').value;
    if(!msg || !chat_id) return alert('Select user and type message');
    await fetch(`chat_send.php?admin_id=${admin_id}&chat_id=${chat_id}&message=${encodeURIComponent(msg)}`);
    document.getElementById('adminMessage').value = '';
    loadChats();
});

// Load settings
async function loadSettings() {
    const admin_id = document.getElementById('admin_id').value.trim();
    const res = await fetch('chat_settings_api.php?admin_id=' + admin_id);
    const data = await res.json();
    document.getElementById('autoDeleteDays').value = data.auto_delete_days || 1;
}

// Save settings
document.getElementById('saveSettingsBtn').addEventListener('click', async ()=>{
    const admin_id = document.getElementById('admin_id').value.trim();
    const days = parseInt(document.getElementById('autoDeleteDays').value);
    await fetch(`chat_settings_api.php?admin_id=${admin_id}&auto_delete_days=${days}`);
    alert('Settings saved!');
});

// Clean old chats
document.getElementById('cleanNowBtn').addEventListener('click', async ()=>{
    const admin_id = document.getElementById('admin_id').value.trim();
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
