
//import { createApp } from "@vue/runtime-dom";
import { createApp } from 'vue';
import Messenger from "./components/messages/Messenger.vue";
import ChatList from "./components/messages/ChatList.vue";


import Echo from 'laravel-echo';

window.Pusher = require('pusher-js');




window.__VUE_PROD_HYDRATION_MISMATCH_DETAILS__ = false;
const chatApp = createApp({
    data() {
        return {
            conversations: [],
            conversation: null,
            messages: [],
            userId: userId,
            csrfToken: csrf_token,
            laravelEcho: null,
            users: [],
            onlineUserIds: [], // Track online user IDs
            chatChannel: null,
            alertAudio: new Audio('/assets/mixkit-correct-answer-tone-2870.wav')
        };
    },

    mounted() {
        this.alertAudio.addEventListener('ended', () => {
            this.alertAudio.currentTime = 0;
        })

        this.laravelEcho = new Echo({
            broadcaster: 'pusher',
            key: process.env.MIX_PUSHER_APP_KEY,
            cluster: process.env.MIX_PUSHER_APP_CLUSTER,
            forceTLS: true,
            encrypted: true
        });

        this.laravelEcho
            .join(`Messenger.${this.userId}`)
            .listen('.new-message', (data) => {
                let exists = false;
                for (let i in this.conversations) {
                    let conversation = this.conversations[i];
                    if (conversation.id === data.message.conversation_id) {
                        if (!conversation.hasOwnProperty('new_messages')) {
                            conversation.new_messages = 0;
                        }
                        conversation.new_messages++;
                        conversation.last_message = data.message;
                        exists = true;
                        this.conversations.splice(i, 1);
                        this.conversations.unshift(conversation);

                        if (this.conversation && this.conversation.id == conversation.id) {
                            this.messages.push(data.message);
                            let container = document.querySelector('#chat-body');
                            container.scrollTop = container.scrollHeight;
                        }
                        break;
                    }
                }
                if (!exists) {
                    fetch(`/api/conversations/${data.message.conversation_id}`)
                        .then(response => response.json())
                        .then(json => {
                            this.conversations.unshift(json)
                        })
                }

                this.alertAudio.play();

            })
            .listen('.message-read', (data) => {
                if (this.conversation && this.conversation.id == data.conversation_id) {
                    this.messages.forEach(message => {
                        if (message.user_id == this.userId) {
                            message.recipients.forEach(recipient => {
                                if (recipient.id == data.user_id) {
                                    if (!recipient.pivot) recipient.pivot = {};
                                    recipient.pivot.read_at = new Date().toISOString();
                                }
                            });
                        }
                    });
                }
            });

        this.chatChannel = this.laravelEcho
            .join('Chat')
            .here((users) => {
                this.onlineUserIds = users.map(u => u.id);
            })
            .joining((user) => {
                if (!this.onlineUserIds.includes(user.id)) {
                    this.onlineUserIds.push(user.id);
                }
            })
            .leaving((user) => {
                this.onlineUserIds = this.onlineUserIds.filter(id => id !== user.id);
            })
            .listenForWhisper('typing', (e) => {
                this.setUserTypingStatus(e.id, e.conversation_id, true);
            })
            .listenForWhisper('stopped-typing', (e) => {
                this.setUserTypingStatus(e.id, e.conversation_id, false);
            });
    },

    methods: {
        moment(time) {
            return moment(time);
        },
        isOnline(userId) {
            return this.onlineUserIds.includes(userId);
        },
        findUser(id, conversation_id) {
            for (let i in this.conversations) {
                let conversation = this.conversations[i];
                if (conversation.id == conversation_id && conversation.participants[0].id == id) {
                    return this.conversations[i].participants[0];
                }
            }
        },
        setUserTypingStatus(userId, conversationId, isTyping) {
            let user = this.findUser(userId, conversationId);
            if (user) {
                user.isTyping = isTyping;
            }
        },
        scrollToBottom() {
            let container = document.querySelector('#chat-body');
            if (container) {
                setTimeout(() => {
                    container.scrollTop = container.scrollHeight;
                }, 100);
            }
        },

        markAsRead(conversation = null) {
            if (conversation == null) {
                conversation = this.conversation;
            }
            fetch(`/api/conversations/${conversation.id}/read`, {
                method: 'PUT',
                mode: 'cors',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    _token: this.$root.csrfToken
                })
            }).then(response => response.json())
                .then(json => {
                    conversation.new_messages = 0;
                })
        },

        deleteMessage(message) {
            fetch(`/api/messages/${message.id}`, {
                method: 'DELETE',
                mode: 'cors',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    _token: this.$root.csrfToken
                })
            }).then(response => response.json())
                .then(json => {
                    // let idx = this.messages.indexOf(message);
                    // this.messages.splice(idx, 1);

                    message.body = 'Message deleted..';
                })
        }
    }
})
chatApp.component('ChatList', ChatList);
chatApp.component('Messenger', Messenger);
chatApp.mount('#chat-app');
