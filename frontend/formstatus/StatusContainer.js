import React from 'react';
import PropTypes from 'prop-types';
import AlertContainer from './AlertContainer';

const StatusContainer = ({msg, formName, bsStyle}) => msg && <AlertContainer formName={formName} msg={msg} bsStyle={bsStyle} />;

StatusContainer.propTypes = {
    msg: PropTypes.string,
    formName: PropTypes.string.isRequired,
    bsStyle: PropTypes.string.isRequired,
};

StatusContainer.defaultProps = {
    msg: null,
};

export default StatusContainer;
