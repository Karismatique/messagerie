
function showMessage(elementId, message, isError = false) {
    const messageDiv = document.getElementById(elementId);
    messageDiv.textContent = message;
    messageDiv.style.color = isError ? 'red' : 'green';
    messageDiv.style.display = 'block';
}


if (document.getElementById('loginForm')) {
    document.getElementById('loginForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        fetch('../api/index.php?action=login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email, password })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
               
                localStorage.setItem('userId', data.userId);
               
                window.location.href = 'chat.html';
            } else {
                showMessage('message', 'Email ou mot de passe incorrect', true);
            }
        })
        .catch(error => {
            showMessage('message', 'Une erreur est survenue', true);
        });
    });
}


if (document.getElementById('registerForm')) {
    document.getElementById('registerForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        fetch('../api/index.php?action=register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email, password })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showMessage('message', 'Inscription réussie ! Redirection vers la page de connexion...', false);
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 2000);
            } else {
                showMessage('message', data.message || 'Erreur lors de l\'inscription', true);
            }
        })
        .catch(error => {
            showMessage('message', 'Une erreur est survenue', true);
        });
    });
}

let userId = null;
let currentConversationId = null;


window.onload = function () {
    userId = localStorage.getItem('userId');
    if (!userId) {
        window.location.href = 'login.html';
    } else {
        loadConversations();
        loadUsers();
    }
};


function loadConversations() {
    fetch(`../api/index.php?action=getConversations&userId=${userId}`)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const conversationsList = document.getElementById('conversationsList');
            conversationsList.innerHTML = '';
            data.conversations.forEach(conversation => {
                const conversationDiv = document.createElement('div');
                conversationDiv.className = 'p-3 hover:bg-gray-100 cursor-pointer rounded-lg';
                conversationDiv.textContent = `Conversation avec ${conversation.otherUserEmail}`;
                conversationDiv.onclick = () => loadMessages(conversation.id);
                conversationsList.appendChild(conversationDiv);
            });
        }
    });
}


function loadUsers() {
    fetch(`../api/index.php?action=getUsers`)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const usersList = document.getElementById('usersList');
            usersList.innerHTML = '';
            data.users.forEach(user => {
                if (user.id !== userId) {
                    const userDiv = document.createElement('div');
                    userDiv.className = 'p-3 hover:bg-gray-100 cursor-pointer rounded-lg';
                    userDiv.textContent = user.email;
                    userDiv.onclick = () => createConversation(user.id);
                    usersList.appendChild(userDiv);
                }
            });
        }
    });
}


function createConversation(otherUserId) {
    fetch('../api/index.php?action=createConversation', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ user1: userId, user2: otherUserId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            currentConversationId = data.conversationId;
            loadMessages(currentConversationId);
            loadConversations();
        }
    });
}


function loadMessages(conversationId) {
    currentConversationId = conversationId;
    fetch(`../api/index.php?action=getMessages&conversationId=${conversationId}`)
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const messagesList = document.getElementById('messagesList');
            messagesList.innerHTML = '';
            data.messages.forEach(message => {
                const messageDiv = document.createElement('div');
                messageDiv.className = message.author === userId ?
                    'bg-blue-500 text-white p-3 rounded-lg self-end max-w-[70%] mb-2' :
                    'bg-gray-200 p-3 rounded-lg self-start max-w-[70%] mb-2';
                messageDiv.textContent = message.content;
                messagesList.appendChild(messageDiv);
            });
            messagesList.scrollTop = messagesList.scrollHeight;
        }
    });
}


function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    if (message && currentConversationId) {
        fetch('../api/index.php?action=sendMessage', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ conversationId: currentConversationId, userId, message })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                messageInput.value = '';
                loadMessages(currentConversationId);
            }
        });
    }

    let lastMessageId = null;


function checkNewMessages() {
    if (currentConversationId) {
        fetch(`../api/index.php?action=checkNewMessages&conversationId=${currentConversationId}&lastMessageId=${lastMessageId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.messages.length > 0) {
                loadMessages(currentConversationId);
                lastMessageId = data.messages[data.messages.length - 1].id;
            }
        });
    }
}


setInterval(checkNewMessages, 5000);
}