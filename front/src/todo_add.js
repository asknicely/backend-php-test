// import React from 'react';

/**
 * props:
 *
 *  {
 *      onAdd: Function(input),
 *      onInvalidInput: Function()
 *  }
 *
 */
class TodoAdd extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            input: null,
        };
    }


    render() {
        return (
            <div>
                <div className="form-group">
                    <input
                        type="text"
                        className="form-control"
                        placeholder="Type a new todo item"
                        value={this.state.value}
                        onChange={(event) => {
                            this.setState({input: event.target.value});
                        }}
                    />
                </div>
                <button
                    type="submit"
                    className="btn btn-default"
                    onClick={() => {
                        let input = this.state.input;
                        // Validate
                        if (input && input.trim().length > 0) {
                            this.props.onAdd(input);
                        } else {
                            this.props.onInvalidInput();
                        }
                    }}>Add
                </button>
            </div>
        );
    }

}