// import React from 'react';

/**
 * props:
 *
 * {
 *     item: {
 *         id,
 *         description,
 *         user_id,
 *         is_completed
 *     },
 *     onDelete: Function(id)
 * }
 */
class TodoItem extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            is_completed: this.props.item.is_completed == 1 ? true : false
        };
    }

    changeCompleteStatus(is_completed) {
        let {id} = this.props.item;

        changeTodoStatus(id, is_completed);
    }

    render() {
        const {
            id,
            description,
            user_id,
        } = this.props.item;

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
                            this.setState((prevState) => {
                                let is_completed = !prevState.is_completed;

                                this.changeCompleteStatus(is_completed);

                                return {
                                    is_completed: is_completed
                                };
                            })
                        }}
                        checked={this.state.is_completed}
                    />
                </td>
                <td>
                    <button
                        type="submit"
                        className="btn btn-xs btn-danger"
                        onClick={() => {
                            this.props.onDelete(id);
                        }}
                    >
                        <span className="glyphicon glyphicon-remove glyphicon-white"/>
                    </button>

                </td>
            </tr>

        );
    }

}