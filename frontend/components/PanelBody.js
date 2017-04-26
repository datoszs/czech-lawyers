import React from 'react';
import PropTypes from 'prop-types';

const PanelBody = ({children}) => (
    <div className="custom-panel-body">
        {children}
    </div>
);

PanelBody.propTypes = {
    children: PropTypes.node.isRequired,
};

export default PanelBody;
