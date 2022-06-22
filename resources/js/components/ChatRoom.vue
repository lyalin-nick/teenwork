<template>
    <main class="container">
        <div class="container p-0">

            <h1 class="h3 mb-3">Messages</h1>

            <div class="card">
                <div class="row g-0">
                    <div class="col-12 col-lg-5 col-xl-3 border-right">

                        <div class="px-4 d-none d-md-block">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <input type="text" class="form-control my-3" placeholder="Search...">
                                </div>
                            </div>
                        </div>

                        <a href="#" class="list-group-item list-group-item-action border-0">
                            <div class="badge bg-success float-right">5</div>
                            <div class="d-flex align-items-start">
                                <img src="https://bootdey.com/img/Content/avatar/avatar5.png"
                                     class="rounded-circle mr-1" alt="Vanessa Tucker" width="40" height="40">
                                <div class="flex-grow-1 ml-3">
                                    Vanessa Tucker
                                    <div class="small"><span class="fas fa-circle chat-online"></span> Online</div>
                                </div>
                            </div>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0">
                            <div class="badge bg-success float-right">2</div>
                            <div class="d-flex align-items-start">
                                <img src="https://bootdey.com/img/Content/avatar/avatar2.png"
                                     class="rounded-circle mr-1" alt="William Harris" width="40" height="40">
                                <div class="flex-grow-1 ml-3">
                                    William Harris
                                    <div class="small"><span class="fas fa-circle chat-online"></span> Online</div>
                                </div>
                            </div>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0">
                            <div class="d-flex align-items-start">
                                <img src="https://bootdey.com/img/Content/avatar/avatar3.png"
                                     class="rounded-circle mr-1" alt="Sharon Lessman" width="40" height="40">
                                <div class="flex-grow-1 ml-3">
                                    Sharon Lessman
                                    <div class="small"><span class="fas fa-circle chat-online"></span> Online</div>
                                </div>
                            </div>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0">
                            <div class="d-flex align-items-start">
                                <img src="https://bootdey.com/img/Content/avatar/avatar4.png"
                                     class="rounded-circle mr-1" alt="Christina Mason" width="40" height="40">
                                <div class="flex-grow-1 ml-3">
                                    Christina Mason
                                    <div class="small"><span class="fas fa-circle chat-offline"></span> Offline</div>
                                </div>
                            </div>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0">
                            <div class="d-flex align-items-start">
                                <img src="https://bootdey.com/img/Content/avatar/avatar5.png"
                                     class="rounded-circle mr-1" alt="Fiona Green" width="40" height="40">
                                <div class="flex-grow-1 ml-3">
                                    Fiona Green
                                    <div class="small"><span class="fas fa-circle chat-offline"></span> Offline</div>
                                </div>
                            </div>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0">
                            <div class="d-flex align-items-start">
                                <img src="https://bootdey.com/img/Content/avatar/avatar2.png"
                                     class="rounded-circle mr-1" alt="Doris Wilder" width="40" height="40">
                                <div class="flex-grow-1 ml-3">
                                    Doris Wilder
                                    <div class="small"><span class="fas fa-circle chat-offline"></span> Offline</div>
                                </div>
                            </div>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0">
                            <div class="d-flex align-items-start">
                                <img src="https://bootdey.com/img/Content/avatar/avatar4.png"
                                     class="rounded-circle mr-1" alt="Haley Kennedy" width="40" height="40">
                                <div class="flex-grow-1 ml-3">
                                    Haley Kennedy
                                    <div class="small"><span class="fas fa-circle chat-offline"></span> Offline</div>
                                </div>
                            </div>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0">
                            <div class="d-flex align-items-start">
                                <img src="https://bootdey.com/img/Content/avatar/avatar3.png"
                                     class="rounded-circle mr-1" alt="Jennifer Chang" width="40" height="40">
                                <div class="flex-grow-1 ml-3">
                                    Jennifer Chang
                                    <div class="small"><span class="fas fa-circle chat-offline"></span> Offline</div>
                                </div>
                            </div>
                        </a>

                        <hr class="d-block d-lg-none mt-1 mb-0">
                    </div>
                    <div class="col-12 col-lg-7 col-xl-9">
                        <div class="py-2 px-4 border-bottom d-none d-lg-block">
                            <div class="d-flex align-items-center py-1">
                                <div class="position-relative">
                                    <img src="https://bootdey.com/img/Content/avatar/avatar3.png"
                                         class="rounded-circle mr-1" alt="Sharon Lessman" width="40" height="40">
                                </div>
                                <div class="flex-grow-1 pl-3">
                                    <strong>{{ this.chatId }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="position-relative">
                                <chat-messages :messages="this.messages" :user-id="this.userId"></chat-messages>
                        </div>
                        <chat-form
                            :user-id="this.userId"
                            v-on:messagesent="addMessage"
                        ></chat-form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
export default {
    name: "ChatRoom",

    props: ['chatId', 'userId'],

    data() {
        return {
            messages: []
        }
    },

    created() {
        this.fetchMessages();

        Echo.private(`chat.${this.chatId}`)
            .listen('.chat.message.send', (e) => {
                let msg = e.message;
                this.messages.push(msg);

                setTimeout(() => {
                    const messages = document.querySelectorAll('.chat-message-right')
                    messages[messages.length - 1].scrollIntoView()
                })
            });
    },

    methods: {
        fetchMessages() {
            axios.get(`/chat/${this.chatId}/messages`).then(response => {
                this.messages = response.data;
            });
        },
        addMessage(message) {
            this.messages.push(message);

            axios.post(`/chat/${this.chatId}/messages`, message).then(response => {
            });
        }
    }
}
</script>

<style scoped>

.chat-online {
    color: #34ce57
}

.chat-offline {
    color: #e4606d
}

.px-4 {
    padding-right: 1.5rem !important;
    padding-left: 1.5rem !important;
}
</style>
