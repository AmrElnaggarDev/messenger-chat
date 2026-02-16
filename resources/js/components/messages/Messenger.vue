<template>
    <!-- Chat -->
    <main class="main is-visible" data-dropzone-area="">
        <div class="container h-100">

            <div class="d-flex flex-column h-100 position-relative">
                <!-- Chat: Header -->
                <ChatHeader :conversation="conversation"/>
                <!-- Chat: Header -->

                <!-- Chat: Content -->
                <ChatContent :conversation="conversation" />
                <!-- Chat: Content -->

                <!-- Chat: Footer -->
                <ChatFooter :conversation="conversation"/>
                <!-- Chat: Footer -->
            </div>

        </div>
    </main>
    <!-- Chat -->

</template>
<script  >
import ChatHeader from "./ChatHeader.vue";
import ChatContent from "./ChatContent.vue";
import ChatFooter from "./ChatFooter.vue";

export default  {
    components: {
        ChatHeader, ChatContent, ChatFooter,
    },
    props: [
        "conversation",
    ],
    data() {
        return {
            messages: []
        };
    }


}
</script>
