<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chat Application</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="{{ asset('css/chat.css') }}" rel="stylesheet">
</head>
<body>
        <div class="top-bar d-flex align-items-center justify-content-between px-3 py-2">
            <div class="logo">
                <img src="{{ asset('images/Logo.png') }}" alt="SLT Logo" height="40">
            </div>
            <div class="search-bar d-flex flex-grow-1 mx-3">
                <input type="text" class="form-control me-2" placeholder="Search contacts, messages or options here...">
                <button class="btn">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            <div class="user-info d-flex align-items-center">
                <img src="{{ asset('images/icon_green.jpeg') }}" alt="User Image" class="rounded-circle">
            </div>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="option-sidebar">
                <div class="icon-option">
                    <i class="bi bi-chat-dots"></i>
                </div>
                <div class="icon-option">
                    <i class="bi bi-gear"></i>
                </div>
                <div class="icon-option">
                    <i class="bi bi-person"></i>
                </div>
                <div class="icon-option">
                    <i class="bi bi-box-arrow-right"></i>
                </div>
            </div>
        
            <div class="col-md-3 contact-sidebar">
                <div class="d-flex align-items-center p-3" style="background-color: #014c9d;">
                    <img src="#" alt="" class="me-2">
                    <span>Contacts</span>
                </div>
                <ul id="contacts" class="contact-list">
                    @foreach ($contacts as $contact)
                        <li class="contact-item" data-phone="{{ $contact->emp_id }}">
                            <img src="{{ asset('images/houman_icon1.png') }}" alt="">
                            <span>{{ $contact->emp_id }}</span>
                        </li>
                    @endforeach
                </ul>
                
                <button id="startChatButton" class="btn btn-primary mt-2">Start Chat</button>
            </div>
            
        
            <!-- Chat Box -->
            <div class="col-md-8 chat-section">
                <div id="contact-info" class="contact-info">
                    <img src="{{ asset('images/houman_icon1.png') }}" alt="">
                    <span>Select a contact to start chatting</span>
                </div>
        
                <div id="chat-box" class="chat-box">
                </div>
        
                <form id="chat-form" class="message-input-group">
                    <input type="text" id="message" class="form-control" placeholder="Type a message">
                <div class="custom-file-upload d-flex align-items-center gap-2">
                <label for="document" class="btn btn-outline-primary d-flex align-items-center">
                   <i class="bi bi-paperclip"></i> Attach File
                </label>
                <input type="file" id="document" name="document" accept=".pdf,.xlsx,.xls,.jpg,.jpeg,.png,.gif,.webp"  class="d-none">
                <span id="file-name" class="text-muted small">No file chosen</span>
                </div>
                    <button type="submit">
                        Send
                    </button>
                </form>
            </div>
        </div>
       
    

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
          const chatBox = document.getElementById('chat-box');
          const contactInfo = document.getElementById('contact-info');
          const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
          let currentContact = '';
          let phoneNumber = '0716520832';
          

document.addEventListener("DOMContentLoaded", function() {
    let phoneNumber = '0716520832';
    const storedAgentId = localStorage.getItem('agent_id');
    const storedPhone = localStorage.getItem('phone_number');
    
    if (storedAgentId && storedPhone) {
        phoneNumber = storedPhone; 
        currentContact = storedAgentId;
        loadMessages(phoneNumber, storedAgentId);
        updateContactListForStoredAgent(storedAgentId);
    }

    // Submit new message with optional document upload
    const requiredSkill = @json($requiredSkill); 
    const language = @json($language);

document.getElementById('chat-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const message = document.getElementById('message').value.trim();
    const documentInput = document.getElementById('document');
    const chatBox = document.getElementById('chat-box');

    let formData = new FormData();
    formData.append('from', phoneNumber);
    formData.append('skill', requiredSkill);
    formData.append('language', language);
    formData.append('message', message);

    if (documentInput.files.length > 0) {
        formData.append('document', documentInput.files[0]);
    }

    if (message || documentInput.files.length > 0) {
        axios.post('/send-message', formData, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'multipart/form-data'
            }
        }).then((response) => {
            // Check if the agent is included in the response and update localStorage
            if (response.data.agent && response.data.agent.emp_id) {
                // Update localStorage with the new agent's emp_id
                localStorage.setItem('agent_id', response.data.agent.emp_id);
                currentContact = response.data.agent.emp_id;  // Optionally store the agent ID elsewhere
                console.log('New agent assigned:', response.data.agent);

                // updateContactInfo(response.data.agent);
            }


            // Create the sent message div
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message sent';

            const timeText = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            if (response.data.document_id) {
                const documentName = documentInput.files.length > 0 ? documentInput.files[0].name : 'Document';
                const documentText = document.createElement('p');
                documentText.textContent = `${documentName}`;
                messageDiv.appendChild(documentText);


                    const documentLink = document.createElement('a');
                    documentLink.href = `/document/view/${message.document_id}`;
                    documentLink.target = '_blank';
                    documentLink.textContent = 'View Document';
                    documentLink.classList.add('document-link');

                    const downloadLink = document.createElement('a');
                    downloadLink.href = `/document/${message.document_id}?download=true`;
                    downloadLink.textContent = 'Download Document';
                    downloadLink.classList.add('download-link');

                    downloadLink.style.marginLeft = '10px';
                    messageDiv.appendChild(documentLink);
                    messageDiv.appendChild(downloadLink);


                if (timeText) {
                    const timeElement = document.createElement('small');
                    timeElement.className = 'text-muted';
                    timeElement.textContent = timeText;
                    messageDiv.appendChild(timeElement);
                }
            } else {
                messageDiv.innerHTML = `<div>${message}</div><small class="text-muted">${timeText}</small>`;
            }

            chatBox.appendChild(messageDiv);
            scrollToBottom();

            document.getElementById('message').value = '';
            documentInput.value = ''; // Clear the file input
            document.getElementById('file-name').textContent = 'No file chosen'; // Reset the displayed file name

        }).catch(error => {
            console.error("Error sending the message!", error);
            alert('Agent is offline! Please try again in a few minutes.');
        });
    }
});

document.getElementById('document').addEventListener('change', function() {
    const fileName = this.files.length > 0 ? this.files[0].name : "No file chosen";
    document.getElementById('file-name').textContent = fileName;
});

// Example of how to update when a new agent is found:
function updateContactInfo(agentId) {
    // This function can be used to update the contact info in the UI
    const contact = document.querySelector(`[data-phone="${agentId}"]`);
    if (contact) {
        const agentInfo = loadAgentInfo(agentId); // Load more agent info if needed (like name or profile details)
        contact.querySelector('span').textContent = agentInfo.name || agentId;
        contact.querySelector('img').src = agentInfo.profile_picture_url || '#';
    }
}


});

function loadMessages(customerPhone, agentEmpId) {
    axios.get(`/messages/${customerPhone}/${agentEmpId}`)
        .then(response => {
            const chatBox = document.getElementById('chat-box');
            chatBox.innerHTML = ''; // Clear previous messages

            if (!response.data || response.data.length === 0) {
                chatBox.innerHTML = '<p>No messages found.</p>';
                return;
            }

            response.data.forEach(message => {
                const messageText = message.message ? message.message : '[No content]';
                const timeText = message.formatted_time ? message.formatted_time : '';
                const messageDiv = document.createElement('div');

                if (message.from === phoneNumber) {
                    messageDiv.className = 'message sent';
                } else {
                    messageDiv.className = 'message received';
                }

                if (message.document_id) {
                    const documentNameText = message.message 
                        ? `Document: ${message.message}`
                        : "";

                    const documentText = document.createElement('p');
                    documentText.textContent = documentNameText;
                    messageDiv.appendChild(documentText);

                    const documentLink = document.createElement('a');
                    documentLink.href = `/document/view/${message.document_id}`;
                    documentLink.target = '_blank';
                    documentLink.textContent = 'View Document';
                    documentLink.classList.add('document-link');

                    const downloadLink = document.createElement('a');
                    downloadLink.href = `/document/${message.document_id}?download=true`;
                    downloadLink.textContent = 'Download Document';
                    downloadLink.classList.add('download-link');

                    downloadLink.style.marginLeft = '10px';
                    messageDiv.appendChild(documentLink);
                    messageDiv.appendChild(downloadLink);

                } else {
                    messageDiv.innerHTML = `<div>${messageText}</div><small class="text-muted">${timeText}</small>`;
                }

                chatBox.appendChild(messageDiv);

                const messageCreatedAt = new Date(message.created_at);
                const now = new Date();
                const diffInMinutes = (now - messageCreatedAt) / 60000;

                if (message.active_chat && diffInMinutes > 2) {
                    deactivateInactiveChat(message._id);
                }
            });

            scrollToBottom();
        })
        .catch(error => {
            console.error('Error loading messages:', error);
        });
}

function deactivateInactiveChats() {
    axios.get('/deactivate-chats') // Calls web.php route
        .then(response => {
            console.log('Inactive chats deactivated:', response.data);
            loadMessages(phoneNumber, currentContact); // Reload chat
        })
        .catch(error => {
            console.error('Error deactivating chats:', error);
        });
}

setInterval(deactivateInactiveChats, 120000);

setInterval(() => {
    if (currentContact) {
        loadMessages(phoneNumber, currentContact);
    }
}, 5000);


document.addEventListener("DOMContentLoaded", function () {
    const storedAgentId = localStorage.getItem('agent_id');
    const storedPhone = localStorage.getItem('phone_number');

    console.log("Stored Agent ID:", storedAgentId);
    console.log("Stored Phone Number:", storedPhone);

    if (storedAgentId && storedPhone) {
        loadMessages(storedPhone, storedAgentId);
        updateContactListForStoredAgent(storedAgentId);
    } else {
        console.warn("Missing agent ID or phone number in localStorage.");
    }
});


function scrollToBottom() {
    chatBox.scrollTop = chatBox.scrollHeight;
}

// // Update contact list when the agent is found
// function updateContactList(agent) {
//     const contactList = document.getElementById('contacts');
    
//     // Check if agent already exists in the contact list
//     if (document.querySelector(`[data-phone="${agent.emp_id}"]`)) {
//         return; 
//     }

//     const contactItem = document.createElement('li');
//     contactItem.className = 'contact-item';
//     contactItem.setAttribute('data-phone', agent.emp_id);
//     contactItem.innerHTML = `
//         <img src="${agent.profile_picture_url || '#'}" alt="">
//         <span>${agent.emp_id}</span>
//     `;

//     // Add click event to load chat when clicked
//     contactItem.addEventListener('click', function () {
//         loadMessages(phoneNumber, agent.emp_id);
//         localStorage.setItem('agent_id', agent.emp_id);
//         localStorage.setItem('phone_number', phoneNumber);
//         currentContact = agent.emp_id;
//         // updateContactInfo(currentContact);
//     });

//     contactList.appendChild(contactItem);
// }


// function updateContactListForStoredAgent(agentId) {
//     const contactList = document.getElementById('contacts');
    
//     // Check if agent already exists in the contact list
//     if (!document.querySelector(`[data-phone="${agentId}"]`)) {
//         const agent = { emp_id: agentId, profile_picture_url: '#' };
//         updateContactList(agent);
//     }
// }


function updateContactList(agent) {
    const contactList = document.getElementById('contacts');

    // Check if agent already exists in the contact list
    const existingContact = document.querySelector(`[data-phone="${agent.emp_id}"]`);

    if (existingContact) {
        // If the agent already exists, you can update their info here
        existingContact.querySelector('span').textContent = agent.emp_id;
        existingContact.querySelector('img').src = agent.profile_picture_url || '#';
        return; 
    }

    // Create a new contact item if the agent doesn't exist
    const contactItem = document.createElement('li');
    contactItem.className = 'contact-item';
    contactItem.setAttribute('data-phone', agent.emp_id);
    contactItem.innerHTML = `
        <img src="{{ asset('images/houman_icon1.png') }}" alt="">
        <span>${agent.emp_id}</span>
    `;

    // Add click event to load chat when clicked
    contactItem.addEventListener('click', function () {
        loadMessages(phoneNumber, agent.emp_id);
        localStorage.setItem('agent_id', agent.emp_id);
        localStorage.setItem('phone_number', phoneNumber);
        currentContact = agent.emp_id;
        updateContactInfo(currentContact);
    });

    contactList.appendChild(contactItem);
}

function updateContactListForStoredAgent(agentId) {
    const contactList = document.getElementById('contacts');
    
    // Check if agent already exists in the contact list
    const existingContact = document.querySelector(`[data-phone="${agentId}"]`);
    
    // If agent does not exist, add a new one
    if (!existingContact) {
        const agent = { emp_id: agentId, profile_picture_url: '#' }; // Example, update with real data
        updateContactList(agent);
    }
}



// Start chat button
document.getElementById('startChatButton').addEventListener('click', function () {
    const customerPhone = '0716520832'; // Example customer phone
    const requiredSkill = 'peo tv'; // Replace dynamically
    const language = 'Tamil'; // Replace dynamically

    axios.post('/match-agent', {
        customer_phone: customerPhone,
        skill: requiredSkill,
        language: language
    })
    .then(response => {
        if (response.data.agent) {
            const agent = response.data.agent; // Matched agent object
            console.log('Matched Agent:', agent.emp_id);

            // Save agent data to localStorage
            localStorage.setItem('matchedAgent', JSON.stringify(agent));

            // Update contact info with agent's details
            const contactInfo = document.getElementById('contact-info');
            contactInfo.innerHTML = `
                <img src="{{ asset('images/houman_icon1.png') }}" alt="">
                <span>${agent.emp_id}</span>
            `;

            updateContactList(agent);

            loadMessages(customerPhone, agent.emp_id);
        } else {
            alert('No available agents found for this skill and language.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('No available agents found! Please try again in a few minutes.');
    });
});


window.addEventListener('load', function () {
    const savedAgent = localStorage.getItem('matchedAgent');
    if (savedAgent) {
        const agent = JSON.parse(savedAgent);
        const customerPhone = '0716520832'; // Example customer phone

        const contactInfo = document.getElementById('contact-info');
        contactInfo.innerHTML = `
            <img src="${agent.profile_picture_url || '#'}" alt="">
            <span>${agent.emp_id}</span>
        `;

        
    }
});


</script>
</body>
</html>
