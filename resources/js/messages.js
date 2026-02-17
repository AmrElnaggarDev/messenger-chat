
//import { createApp } from "@vue/runtime-dom";
import { createApp } from 'vue';
import Messenger from "./components/messages/Messenger.vue";
import ChatList from "./components/messages/ChatList.vue";


 import Echo from 'laravel-echo';

 window.Pusher = require('pusher-js');




const chatApp = createApp({
    data() {
        return {
            conversations: [],
            conversation: null,
            messages: [],
            userId: userId,
            csrfToken: csrf_token,
            laravelEcho: null,
            users: []
        };
    },

    mounted() {
        this.laravelEcho = new Echo({
            broadcaster: 'pusher',
            key: process.env.MIX_PUSHER_APP_KEY,
            cluster: process.env.MIX_PUSHER_APP_CLUSTER,
            forceTLS: true
        });

        this.laravelEcho
            //.join(`Messenger.${this.userId}`)
            .join(`Messenger`)
            .joining( (user) => {
                for (let i in this.conversations) {
                    let conversation = this.conversations[i];
                    if (conversation.participants[0].id === user.id){
                        console.log(user);
                         this.conversations[i].participants[0].isOnline = true;
                        return;
                    }
                }
            })
            .leaving( (user) => {
                for (let i in this.conversations) {
                    let conversation = this.conversations[i];
                    if (conversation.participants[0].id === user.id){
                        console.log(user);
                         this.conversations[i].participants[0].isOnline = true;
                        return;
                    }
                }
            })
            .listen('.new-message', (data) => {
                alert(data.message.body);
                this.messages.push(data.message);
                let container = document.querySelector('#chat-body');
                container.scrollTop = container.scrollHeight;
            });
    },

    methods: {
        moment(time) {
            return moment (time);
        },
        isOnline(user )  {
            for (let i in this.users) {
                if (this.users[i].id === user.id){
                    return this.users[i].isOnline;
                }
            }
            return false;
        },
    }
})
    chatApp.component('ChatList', ChatList);
    chatApp.component('Messenger', Messenger);
    chatApp.mount ('#chat-app');
