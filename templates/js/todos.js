import Vue from 'vue';
import Todos from './components/Todos.vue';

new Vue({
    el: 'todos',
    render: h => h(Todos)
});
