function checkForNewMessages() {
    fetch('../Public/Chats/checkNewMessages.php')
        .then(response => response.json())
        .then(data => {
            if (data.newMessages) {
                console.log(data.newMessages);
                // Play notification sound only if the user has interacted with the page
                document.addEventListener('click', function() {
                    var audio = new Audio('../Public/Chats/notification.wav');
                    audio.play();
                })
            }
        })
        .catch(error => console.error('Error fetching new messages:', error));
}

setInterval(checkForNewMessages, 1000);
