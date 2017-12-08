import React, {Component} from 'react';
import PropTypes from 'prop-types';
import ChildrenDisplay from './ChildrenDisplay';
import selectFieldContextType from './selectFieldContextType';

class SelectField extends Component {
    getChildContext() {
        return {
            selectField: {
                name: this.props.name,
                required: this.props.required,
            },
        };
    }

    render() {
        return React.createElement(ChildrenDisplay, this.props, this.props.children);
    }
}

SelectField.propTypes = {
    name: PropTypes.string.isRequired,
    required: PropTypes.bool,
    children: PropTypes.node.isRequired,
};

SelectField.defaultProps = {
    required: false,
};

SelectField.childContextTypes = {
    selectField: selectFieldContextType,
};

export default SelectField;
