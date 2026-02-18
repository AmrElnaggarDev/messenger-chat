
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
            chatChannel: null,
        };
    },

    mounted() {
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
                this.messages.push(data.message);
                this.scrollToBottom();
            });

        this.chatChannel = this.laravelEcho
            .join('Chat')
            .joining((user) => {
                this.updateUserStatus(user.id, true);
            })
            .leaving((user) => {
                this.updateUserStatus(user.id, false);
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
        isOnline(user) {
            for (let i in this.users) {
                if (this.users[i].id === user.id) {
                    return this.users[i].isOnline;
                }
            }
            return false;
        },
        findUser(id, conversation_id) {
            for (let i in this.conversations) {
                let conversation = this.conversations[i];
                if (conversation.id == conversation_id && conversation.participants[0].id == id) {
                    return this.conversations[i].participants[0];
                }
            }
        },
        updateUserStatus(userId, isOnline) {
            for (let i in this.conversations) {
                let conversation = this.conversations[i];
                if (conversation.participants[0].id === userId) {
                    this.conversations[i].participants[0].isOnline = isOnline;
                    return;
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
        }
    }
})
chatApp.component('ChatList', ChatList);
chatApp.component('Messenger', Messenger);
chatApp.mount('#chat-app');
