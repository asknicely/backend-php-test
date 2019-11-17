import axios from "axios";

export default {
    all() {
        return axios.get('/api/v1/todo');
    },

    store(data) {
        return axios.post('/api/v1/todo/add', data);
    },

    delete(id) {
        return axios.delete('/api/v1/todo/' + id);
    },

    show(id) {
        return axios.get('/api/v1/todo/' + id);
    },

    update(id, data) {
        return axios.patch('/api/v1/todo/' + id, data);
    }
}
