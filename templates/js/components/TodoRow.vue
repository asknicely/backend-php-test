<template>
    <tr>
        <td># {{ todo.id }}</td>
        <td>
            <input type="checkbox" id="checkbox" v-model="isCompleted" />
        </td>
        <td>{{ todo.username }}</td>
        <td>
            <a :href="'/todo/' + todo.id">
                {{ todo.description }}
            </a>
        </td>
        <td>
            <button v-on:click="deleteTodo(todo.id)" class="btn btn-xs btn-danger">
                <span class="glyphicon glyphicon-remove glyphicon-white"></span>
            </button>
        </td>
                </tr>
</template>
<script>
    import Vue from 'vue';
    import VueToast from 'vue-toast-notification';
    import 'vue-toast-notification/dist/index.css';
    import api from '../api/todo';
    Vue.use(VueToast);

    export default {
        props: {
            todo: {
                type: Object,
                required: true
            },
        },
        computed: {
            isCompleted: {
                get: function() {
                    return this.todo.completed === '1';
                },
                set: function(val) {
                    const data = {
                        completed: val,
                    };

                    api.update(this.todo.id, data).then(response => {
                        this.$emit('updated')
                    }).catch(response => {
                        Vue.$toast.error('Unable to update a todo');
                    });
                },
            },
        },
        methods: {
            deleteTodo(id) {
                api.delete(id).then(response => {
                    this.$emit('deleted')
                }).catch(response => {
                        Vue.$toast.error('Unable to delete a todo');
                });
            },
        }
    }
</script>