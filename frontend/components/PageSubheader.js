import React, {PropTypes} from 'react';

const PageSubheader = ({children}) => (
    <div className="page-subheader">
        <h2>{children}</h2>
        <hr />
    </div>
);

PageSubheader.propTypes = {
    children: PropTypes.node.isRequired,
};

export default PageSubheader;
