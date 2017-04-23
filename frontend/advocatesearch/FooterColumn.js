import React, {PropTypes} from 'react';
import {Col} from 'react-bootstrap';

const FooterColumn = ({value, label}) => (
    <Col sm={4}>{label && `${label}: ` }<b>{value}</b></Col>
);

FooterColumn.propTypes = {
    value: PropTypes.string.isRequired,
    label: PropTypes.string,
};

FooterColumn.defaultProps = {
    label: null,
};

export default FooterColumn;
