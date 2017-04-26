import React from 'react';
import PropTypes from 'prop-types';
import {Radio} from 'react-bootstrap';

const SelectOptionComponent = ({input, id, children}) => <Radio value={id} checked={input.value === id} onChange={input.onChange}>{children}</Radio>;

SelectOptionComponent.propTypes = {
    input: PropTypes.shape({
        value: PropTypes.string,
        onChange: PropTypes.func.isRequired,
    }).isRequired,
    id: PropTypes.string.isRequired,
    children: PropTypes.node.isRequired,
};

export default SelectOptionComponent;
