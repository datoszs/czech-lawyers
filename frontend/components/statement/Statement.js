import React from 'react';
import PropTypes from 'prop-types';
import {Well} from 'react-bootstrap';

const Statement = ({children, onClick}) => (
    <Well onClick={onClick} className="problem-statement">
        {children}
    </Well>
);

Statement.propTypes = {
    children: PropTypes.node.isRequired,
    onClick: PropTypes.func.isRequired,
};

export default Statement;
