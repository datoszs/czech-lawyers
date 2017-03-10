import React, {PropTypes} from 'react';

const TwoColumn = ({children}) => <div className="two-column">{children}</div>;

TwoColumn.propTypes = {
    children: PropTypes.node,
};

TwoColumn.defaultProps = {
    children: null,
};

export default TwoColumn;
