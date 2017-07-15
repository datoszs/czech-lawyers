import React from 'react';
import PropTypes from 'prop-types';

const ThreeColumn = ({children}) => <div className="three-column">{children}</div>;

ThreeColumn.propTypes = {
    children: PropTypes.node,
};

ThreeColumn.defaultProps = {
    children: null,
};

export default ThreeColumn;
