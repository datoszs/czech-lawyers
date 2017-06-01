import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {Alert} from 'react-bootstrap';
import {LoadingAlert} from '../../components';
import {Msg} from '../../containers';
import {getResult} from './selectors';
import {resultStyle, resultMsg} from './constants';

const ResultContainer = ({result}) => {
    if (result) {
        const bsStyle = resultStyle[result] || 'danger';
        const msg = resultMsg[result] || 'case.dispute.verify.fail';
        return <Alert bsStyle={bsStyle}><Msg msg={msg} /></Alert>;
    } else {
        return <LoadingAlert><Msg msg="loading.alert" /></LoadingAlert>;
    }
};

ResultContainer.propTypes = {
    result: PropTypes.string,
};

ResultContainer.defaultProps = {
    result: null,
};

const mapStateToProps = (state) => ({
    result: getResult(state),
});

export default connect(mapStateToProps)(ResultContainer);
