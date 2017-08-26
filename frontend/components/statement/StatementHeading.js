import React from 'react';
import PropTypes from 'prop-types';
import {Well} from 'react-bootstrap';

const StatementHeading = ({children}) => (
    <Well className="statement-heading">
        {children}
    </Well>
);

StatementHeading.propTypes = {
    children: PropTypes.node.isRequired,
};

export default StatementHeading;
