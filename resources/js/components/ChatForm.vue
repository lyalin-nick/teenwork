<template>

    <div class="flex-grow-0 py-3 px-4 border-top">
        <div class="input-group">
            <input id="btn-input" type="text" class="form-control" placeholder="Type your message" v-model="newMessage" @keyup.enter="sendMessage">
            <button class="btn btn-primary" id="btn-chat" @click="sendMessage">Send</button>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['userId'],

        data() {
            return {
                newMessage: ''
            }
        },

        methods: {
            sendMessage() {

                this.$emit('messagesent', {
                    user_info: {
                        id: this.userId,
                        name: "Admin Teenswork",
                        photo: "/img/matchagency.png"
                    },
                    text: this.newMessage
                });

                this.newMessage = ''
            }
        }
    }
</script>
<style scoped>
body {
    margin-top: 20px;
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
