
import { createApp } from "@vue/runtime-dom";
import Messenger from "./components/messages/Messenger.vue";
import ChatList from "./components/messages/ChatList.vue";


const chatApp = createApp({
    data() {
        return {
            conversation: null,
            messages: []
        };
    }
})
    chatApp.component('ChatList', ChatList);
    chatApp.component('Messenger', Messenger);
    chatApp.mount ('#chat-app');
