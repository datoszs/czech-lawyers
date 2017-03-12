import React, {PropTypes} from 'react';

const PanelBody = ({children}) => (
    <div className="custom-panel-body">
        {children}
    </div>
);

PanelBody.propTypes = {
    children: PropTypes.node.isRequired,
};

export default PanelBody;
