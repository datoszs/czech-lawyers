import React from 'react';
import PropTypes from 'prop-types';

const DetailFieldComponent = ({label, children}) => (
    <div className="detail-field">
        <div className="detail-field-label">{label}</div>
        <div className="detail-field-value">{children}</div>
    </div>
);

DetailFieldComponent.propTypes = {
    label: PropTypes.string,
    children: PropTypes.node,
};

DetailFieldComponent.defaultProps = {
    children: null,
    label: '',
};

export default DetailFieldComponent;
