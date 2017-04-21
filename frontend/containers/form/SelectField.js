import React, {Component, PropTypes} from 'react';
import {FormGroup, ControlLabel} from 'react-bootstrap';
import Msg from '../Msg';

const SelectField = class extends Component {
    getChildContext() {
        return {
            selectName: this.props.name,
        };
    }

    render() {
        return (
            <FormGroup>
                {this.props.label && <ControlLabel><Msg msg={this.props.label} /></ControlLabel>}
                {this.props.children}
            </FormGroup>
        );
    }
};

SelectField.propTypes = {
    name: PropTypes.string.isRequired,
    label: PropTypes.string,
    children: PropTypes.node.isRequired,
};

SelectField.defaultProps = {
    label: null,
};

SelectField.childContextTypes = {
    selectName: PropTypes.string.isRequired,
};

export default SelectField;
