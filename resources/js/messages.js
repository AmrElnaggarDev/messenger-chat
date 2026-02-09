
import { createApp } from "@vue/runtime-dom";
import Messenger from "./components/messages/Messenger.vue";


const chatApp = createApp({})
    .component('Messenger', Messenger)
    .mount ('#chat-app');
