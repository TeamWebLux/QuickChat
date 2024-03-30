function checkForNewMessages() {
    fetch('../Public/Chats/checkNewMessages.php')
        .then(response => response.json())
        .then(data => {
            if (data.newMessages) {
                // Play notification sound only if the user has interacted with the page
                document.addEventListener('click', function() {
                    var audio = new Audio('./notification.wav');
                    audio.play();
                }, { once: true }); // { once: true } ensures that the event listener is removed after the first click
            }
        })
        .catch(error => console.error('Error fetching new messages:', error));
}

// Poll for new messages every 30 seconds
setInterval(checkForNewMessages, 3000);
