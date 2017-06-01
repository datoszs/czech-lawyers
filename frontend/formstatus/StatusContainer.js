import React from 'react';
import PropTypes from 'prop-types';
import {Alert} from 'react-bootstrap';
import {LifecycleListener} from '../util';
import {Msg} from '../containers';

const AlertComponent = LifecycleListener(Alert);

const StatusContainer = ({msg, bsStyle, handleClear}) =>
msg && (
    <AlertComponent
        bsStyle={bsStyle}
        onDismiss={handleClear}
        onUnmount={handleClear}
    >
        <Msg msg={msg} />
    </AlertComponent>
);

StatusContainer.propTypes = {
    text: PropTypes.string,
    bsStyle: PropTypes.string.isRequired,
    handleClear: PropTypes.func.isRequired,
};

StatusContainer.defaultProps = {
    msg: null,
};

export default StatusContainer;
