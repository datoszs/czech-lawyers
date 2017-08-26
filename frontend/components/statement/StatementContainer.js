import React from 'react';
import PropTypes from 'prop-types';

const StatementContainer = ({children}) => (
    <div className="statement-container">
        {children}
    </div>
);

StatementContainer.propTypes = {
    children: PropTypes.node.isRequired,
};

export default StatementContainer;
