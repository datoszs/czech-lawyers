import React from 'react';
import PropTypes from 'prop-types';

const Center = ({children}) => <div className="horizontal-centering">{children}</div>;

Center.propTypes = {
    children: PropTypes.node.isRequired,
};

export default Center;
