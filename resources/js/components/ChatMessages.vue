<template>
    <div class="position-relative">
        <div class="chat-messages p-4" v-for="message in messages">
            <template v-if="message.user_info.id === userId">
                <div class="chat-message-right pb-4">
                    <div>
                        <img v-if="message.user_info.photo" :src="message.user_info.photo" class="rounded-circle mr-1"
                             width="40" height="40">
                        <img v-else src="/img/matchagency.png" class="rounded-circle mr-1"
                             width="40" height="40">
                    </div>
                    <div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3">
                        <div class="font-weight-bold mb-1">{{ message.user_info.name }}</div>
                        {{ message.text }}
                        <div class="position-relative">
                            <img v-if="message.img" :src="message.img">
                        </div>
                    </div>
                </div>
            </template>
            <template v-else>

                <div class="chat-message-left pb-4">
                    <div>
                        <img v-if="message.user_info.photo" :src="message.user_info.photo" class="rounded-circle mr-1"
                             width="40" height="40">
                    </div>
                    <div class="flex-shrink-1 bg-light rounded py-2 px-3 ml-3">
                        <div class="font-weight-bold mb-1">{{ message.user_info.name }}</div>
                        {{ message.text }}
                        <div class="position-relative">
                            <img v-if="message.img" :src="message.img">
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
export default {
    props: ['messages', 'userId']
};
</script>
<style scoped>
body {
    margin-top: 20px;
}

.chat-online {
    color: #34ce57
}

.chat-offline {
    color: #e4606d
}

.chat-messages {
    display: flex;
    flex-direction: column;
    max-height: 800px;
    overflow-y: scroll
}

.chat-message-left,
.chat-message-right {
    display: flex;
    flex-shrink: 0
}

.chat-message-left {
    margin-right: auto
}

.chat-message-right {
    flex-direction: row-reverse;
    margin-left: auto
}

.py-3 {
    padding-top: 1rem !important;
    padding-bottom: 1rem !important;
}

.px-4 {
    padding-right: 1.5rem !important;
    padding-left: 1.5rem !important;
}

.flex-grow-0 {
    flex-grow: 0 !important;
}

.border-top {
    border-top: 1px solid #dee2e6 !important;
}
</style>
