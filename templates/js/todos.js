import Vue from 'vue';
import Todos from './components/Todos.vue';

if (document.getElementById('todos')) {
    new Vue({
        el: '#todos',
        render: h => h(Todos)
    });
}
