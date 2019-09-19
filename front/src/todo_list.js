// import React from 'react';

const ID = "todos_list";

class TodoList extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            todos: []
        };
    }

    componentDidMount() {
        fetch("/api/todos").then((data) => data.json()).then((data) => {
            console.log(data);
            this.setState({
                todos: data
            })
        }).catch((err) => {
            console.log(err);
        })
    }


    render() {
        return (
            <div>
                <table className="table table-striped">
                    <tbody>
                    <th>#</th>
                    <th>User</th>
                    <th>Description</th>
                    <th>Is Done</th>

                    <th></th>

                    {
                        this.state.todos.map((item) => {
                            const {
                                id,
                                description,
                                user_id,
                                is_completed,
                            } = item;
                            return (
                                <tr key={id}>
                                    <td>{id}</td>
                                    <td>{user_id}</td>
                                    <td>
                                        <a href={`/todo/${id}`}>
                                            {description}
                                        </a>
                                    </td>
                                    <td>
                                        <input
                                            type="checkbox"
                                            onChange={() => {

                                            }}
                                            checked={is_completed == 1}
                                        />
                                    </td>
                                    <td>

                                        <button type="submit" className="btn btn-xs btn-danger"><span
                                            className="glyphicon glyphicon-remove glyphicon-white"></span></button>

                                    </td>
                                </tr>
                            );
                        })
                    }

                    </tbody>
                </table>


            </div>

        );
    }
}

let domContainer = document.querySelector('#' + ID);
ReactDOM.render(<TodoList/>, domContainer);