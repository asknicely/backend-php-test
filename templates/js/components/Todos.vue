<template>
    <div class="col-md-4 col-md-offset-4">
        <h1>Todo List:</h1>
        <table class="table table-striped">
            <tbody>
                <tr>
                    <th>#</th>
                    <th>Completed</th>
                    <th>User</th>
                    <th>Description</th>
                    <th></th>
                </tr>
                <todo-row
                    v-for="todo in todos"
                    :todo="todo"
                    :key="todo.id"
                    @deleted="deleted"
                    @updated="updated"
                />
                <tr>
                    <td colspan="4">
                        <input v-model="description" placeholder="Description..." class="small-6 small-center">
                    </td>
                    <td>
                        <button v-on:click="addTodo()" :disabled="isDescriptionEmpty" class="btn btn-sm btn-primary">Add</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
    import Vue from 'vue';
    import VueToast from 'vue-toast-notification';
    import 'vue-toast-notification/dist/index.css';
    import TodoRow from './TodoRow.vue';
    import api from '../api/todo';
    import { isEmpty } from 'lodash';
    Vue.use(VueToast);

    export default {
        components: {
            TodoRow,
        },
        computed: {
            isDescriptionEmpty() {
                return isEmpty(this.description);
            }
        },
        methods: {
            isCompleted(value) {
                return false;
            },
            addTodo() {
                const data = {
                    description: this.description,
                };

                api.store(data).then(response => {
                    this.description = '';
                    this.loadTodos();

                    Vue.$toast.open('A todo has been added');
                }).catch(response => {
                    Vue.$toast.error('Unable to save a todo');
                });
            },
            deleted() {
                this.loadTodos();
                Vue.$toast.open('A todo has been deleted');
            },
            updated() {
                Vue.$toast.open('A todo has been updated');
            },
            loadTodos() {
                api.all().then(response => {
                    this.todos = response.data
                }).catch(response => {
                    Vue.$toast.error('Unable to load the list of todos');
                });
            }
        },

        data() {
            return {
                todos: [],
                description: '',
            }
        },

        mounted() {
            this.loadTodos();
        },
    }
</script>