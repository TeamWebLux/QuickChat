function checkForNewMessages() {
    fetch('./checkNewMessages.php')
        .then(response => response.json())
        .then(data => {
            if (data.newMessages) {
                // Play notification sound
                var audio = new Audio('./notification.wav');
                audio.play();
            }
        })
        .catch(error => console.error('Error fetching new messages:', error));
}

// Poll for new messages every 30 seconds
setInterval(checkForNewMessages, 30000);
